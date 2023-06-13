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

                $matched = $triggerIsRegex
                    ? \preg_match($data['@triggerGroup'], $hook) === 1
                    : $data['@triggerGroup'] === $hook;

                if ($matched) {
                    $this->runWorkflow($workflow, $hook, $data);
                } elseif (\stripos($hook, '/') === 0) {
                    $matched = \preg_match($hook, $data['@triggerGroup']) === 1;
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
            $response->data['instance_create'] = new FormValidation($val);
            $response->header->status          = RequestStatusCode::R_400;
        }

        $instance = $this->createInstanceFromRequest($request);
        $this->createModel($request->header->account, $instance, WorkflowInstanceAbstractMapper::class, 'instance', $request->getOrigin());
        $this->startInstance($request, $response, $instance);

        $new      = clone $instance;
        $new->end = new \DateTimeImmutable('now');
        $this->updateModel($request->header->account, $instance, $new, WorkflowInstanceAbstractMapper::class, 'instance', $request->getOrigin());

        $view->setTemplate('/Modules/Workflow/Theme/Cli/empty-command');

        return $view;
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
        if (($val['id'] = !$request->hasData('-id'))) {
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
            ->where('id', (int) $request->getData('-id'))
            ->execute();

        $instance           = new WorkflowInstance();
        $instance->template = $template;

        return $instance;
    }

    /**
     * Start a workflow instance
     *
     * @param RequestAbstract          $request  Request
     * @param ResponseAbstract         $response Response
     * @param WorkflowInstanceAbstract $instance Workflow instance
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function startInstance(RequestAbstract $request, ResponseAbstract $response, WorkflowInstanceAbstract $instance) : void
    {
        $actionString = \file_get_contents(__DIR__ . '/../Definitions/actions.json');
        if ($actionString === false) {
            return;
        }

        $actions = \json_decode($actionString, true);
        if (!\is_array($actions)) {
            return;
        }

        foreach ($instance->template->schema as $e) {
            if ($e['id'] === $request->getDataString('-trigger')) {
                $this->runWorkflowElement($request, $response, $actions, $instance, $e);

                break;
            }
        }
    }

    /**
     * Run a workflow element
     *
     * @param RequestAbstract          $request  Request
     * @param ResponseAbstract         $response Response
     * @param array                    $actions  All available actions
     * @param WorkflowInstanceAbstract $instance Current workflow instance
     * @param array                    $element  Workflow element to run
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function runWorkflowElement(
        RequestAbstract $request, ResponseAbstract $response,
        array $actions, WorkflowInstanceAbstract $instance, array $element
    ) : void
    {
        if (isset($actions[(int) $element['id']])
            && $actions[(int) $element['id']]['function'] !== ''
        ) {
            $this->app->moduleManager
                ->get($actions[(int) $element['id']]['module'], $actions[(int) $element['id']]['function_type'])
                ->{$actions[(int) $element['id']]['function']}($request, $response, [$element]);
        }

        // @todo: currently all children are executed one after another, maybe consider parallel execution
        foreach ($element['children'] as $child) {
            // @todo: pass previous results (probably needs a populator for input variables based on previous output variables)
            $this->runWorkflowElement($request, $response, $actions, $instance, $child);
        }
    }
}
