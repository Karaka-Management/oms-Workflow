<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Controller;

use Modules\Workflow\Models\WorkflowInstance;
use Modules\Workflow\Models\WorkflowInstanceAbstract;
use Modules\Workflow\Models\WorkflowInstanceAbstractMapper;
use Modules\Workflow\Models\WorkflowStatus;
use Modules\Workflow\Models\WorkflowTemplate;
use Modules\Workflow\Models\WorkflowTemplateMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Utils\StringUtils;
use phpOMS\Views\View;

/**
 * Workflow controller class.
 *
 * @package Modules\Workflow
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class CliController extends Controller
{
    /**
     * Api method to make a call to the cli app
     *
     * @param mixed ...$data Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflowFromHook(mixed ...$data) : void
    {
        /** @var \Modules\Workflow\Models\WorkflowTemplate[] $workflows */
        $workflows = WorkflowTemplateMapper::getAll()->where('status', WorkflowStatus::ACTIVE)->execute();
        foreach ($workflows as $workflow) {
            $hooks = $workflow->getHooks();

            foreach ($hooks as $hook) {
                /** @var array{':triggerGroup'?:string} $data */
                $triggerIsRegex = \stripos($data['@triggerGroup'], '/') === 0;
                $matched        = false;

                if ($triggerIsRegex) {
                    $matched = \preg_match($data['@triggerGroup'], $hook) === 1;
                } else {
                    $matched = $data['@triggerGroup'] === $hook;
                }

                if (!$matched && \stripos($hook, '/') === 0) {
                    $matched = \preg_match($hook, $data['@triggerGroup']) === 1;
                }

                if ($matched) {
                    $this->runWorkflow($workflow, $hook, $data);
                }
            }
        }
    }

    /**
     * Api method to make a call to the cli app
     *
     * @param WorkflowTemplate $workflow Workflow template
     * @param string           $hook     Event hook
     * @param array            $data     Event data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflow(WorkflowTemplate $workflow, string $hook, array $data) : void
    {
        include $workflow->source->getAbsolutePath();
    }

    /**
     * Method which creates a workflow instance
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface Response can be rendered
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function cliWorkflowInstanceCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        if (!empty($val = $this->validateInstanceCreate($request))) {
            $response->set('instance_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;
        }

        $instance = $this->createInstanceFromRequest($request);

        $view->setTemplate('/Modules/Workflow/Theme/Cli/empty-command');

        return $view;
    }

    public function runWorkflowElement(array $actions, WorkflowTemplate $template, WorkflowInstanceAbstract $instance, array $element) : void
    {
        if (isset($actions[$element['id']])) {
            $result = $this->app->moduleManager
                ->get($actions[$element['id']]['modules'], $actions[$element['id']]['function_type'])
                ->{$actions[$element['id']]['function']}($template);
        }

        // @todo: currently all children are executed one after another, maybe consider parallel execution
        foreach ($element['children'] as $child) {
            // @todo: pass previous results (probably needs a populator for input variables based on previous output variables)
            $this->runWorkflowElement($actions, $template, $instance, $child);
        }
    }

    /**
     * Validate template create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateInstanceCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create instance from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return WorkflowInstanceAbstract
     *
     * @todo: How to handle workflow instances which are not saved in the database and are continued?
     *
     * @since 1.0.0
     */
    private function createInstanceFromRequest(RequestAbstract $request) : WorkflowInstanceAbstract
    {
        /** @var \Modules\Workflow\Models\WorkflowTemplate $template */
        $template = WorkflowTemplateMapper::get()
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $instance = new WorkflowInstance();

        $actionString = \file_get_contents(__DIR__ . '/../Definitions/actions.json');
        if ($actionString === false) {
            return $instance;
        }

        $actions = \json_decode($actionString, true);
        if (!\is_array($actions)) {
            return $instance;
        }

        foreach ($template->schema as $e) {
            if ($e['id'] === $request->getDataString('trigger')) {
                $this->runWorkflowElement($actions, $template, $instance, $e);

                break;
            }
        }

        return $instance;
    }
}
