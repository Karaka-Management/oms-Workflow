<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Controller;

use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\NullMedia;
use Modules\Workflow\Models\WorkflowInstanceMapper;
use Modules\Workflow\Models\WorkflowStatus;
use Modules\Workflow\Models\WorkflowTemplateMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Workflow class.
 *
 * @package Modules\Workflow
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class BackendController extends Controller
{
    /**
     * Api method to make a call to the cli app
     *
     * @param mixed $data Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflowFromHook(...$data) : void
    {
        $workflows = WorkflowTemplateMapper::getAll()->where('status', WorkflowStatus::ACTIVE)->execute();
        foreach ($workflows as $workflow) {
            $hooks = $workflow->getHooks();

            foreach ($hooks as $hook) {
                $triggerIsRegex = \stripos($data[':triggerGroup'], '/') === 0;
                $matched = false;

                if ($triggerIsRegex) {
                    $matched = \preg_match($data[':triggerGroup'], $hook) === 1;
                } else {
                    $matched = $data[':triggerGroup'] === $hook;
                }

                if (!$matched && \stripos($hook, '/') === 0) {
                    $matched = \preg_match($hook, $data[':triggerGroup']) === 1;
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
     * @param mixed $data Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflow(WorkflowTemplate $workflow, string $hook, array $data) : void
    {
        include $workflow->media->getAbsolutePath();
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewWorkflowTemplateList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-template-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        $path      = \str_replace('+', ' ', (string) ($request->getData('path') ?? '/'));
        $templates = WorkflowTemplateMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('virtualPath', $path)
            ->where('tags/title/language', $response->getLanguage())
            ->execute();

        list($collection, $parent) = CollectionMapper::getCollectionsByPath($path);

        $view->addData('parent', $parent);
        $view->addData('collections', $collection);
        $view->addData('path', $path);
        $view->addData('reports', $templates);
        $view->addData('account', $this->app->accountManager->get($request->header->account));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewWorkflowTemplate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        $template = WorkflowTemplateMapper::get()
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        require_once $template->findFile('WorkflowController.php')->getPath();

        /** @var WorkflowControllerInterface $controller */
        $controller = new WorkflowController($this->app, $template);

        // @todo load template specific data and pass it to the view

        if (!(($list = $template->findFile('template-profile.tpl.php')) instanceof NullMedia)) {
            $view->setTemplate('/' . \substr($list->getPath(), 0, -8));
        } else {
            $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-profile');
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewWorkflowTemplateCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-template-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-dashboard');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewInstanceList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        $template = WorkflowTemplateMapper::get()
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        require_once $template->findFile('WorkflowController.php')->getPath();

        /** @var WorkflowControllerInterface $controller */
        $controller = new WorkflowController($this->app, $template);

        $view->addData('instances', $controller->getInstanceListFromRequest($request));

        if (!(($list = $template->findFile('instance-list.tpl.php')) instanceof NullMedia)) {
            $view->setTemplate('/' . \substr($list->getPath(), 0, -8));
        } else {
            $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-instance-list');
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewInstance(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        $template = WorkflowTemplateMapper::get()
            ->where('template', (int) $request->getData('template'))
            ->execute();

        require_once $template->findFile('WorkflowController.php')->getPath();

        /** @var WorkflowControllerInterface $controller */
        $controller = new WorkflowController($this->app, $template);

        $view->addData('instance', $controller->getInstanceFromRequest($request));

        if (!(($instance = $template->findFile('instance-profile.tpl.php')) instanceof NullMedia)) {
            $view->setTemplate('/' . \substr($instance->getPath(), 0, -8));
        } else {
            $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-instance');
        }

        return $view;
    }
}
