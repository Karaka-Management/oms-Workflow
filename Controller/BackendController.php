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

use Modules\Media\Models\CollectionMapper;
use Modules\Workflow\Models\WorkflowControllerInterface;
use Modules\Workflow\Models\WorkflowInstanceAbstractMapper;
use Modules\Workflow\Models\WorkflowTemplateMapper;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Workflow class.
 *
 * @package Modules\Workflow
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class BackendController extends Controller
{
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
    public function viewWorkflowTemplateList(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-template-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response);

        $path      = \str_replace('+', ' ', (string) ($request->getData('path') ?? '/'));
        $templates = WorkflowTemplateMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('virtualPath', $path)
            ->where('tags/title/language', $response->header->l11n->language)
            ->execute();

        list($collection, $parent) = CollectionMapper::getCollectionsByPath($path);

        $view->data['parent']      = $parent;
        $view->data['collections'] = $collection;
        $view->data['path']        = $path;
        $view->data['reports']     = $templates;
        $view->data['account']     = $this->app->accountManager->get($request->header->account);

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
    public function viewWorkflowTemplate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view              = new View($this->app->l11nManager, $request, $response);
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response);

        /** @var \Modules\Workflow\Models\WorkflowTemplate $template */
        $template = WorkflowTemplateMapper::get()
            ->with('source')
            ->with('source/sources')
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->data['template'] = $template;

        if ($template->source->findFile('template-profile.tpl.php')->id > 0) {
            require_once $template->source->findFile('WorkflowController.php')->getPath();

            /** @var WorkflowControllerInterface $controller */
            $controller = new \Modules\Workflow\Controller\WorkflowController($this->app, $template);
            $controller->createTemplateViewFromRequest($view, $request, $response);
        } else {
            $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-template');
        }

        $head = $response->get('Content')->head;
        $head->addAsset(AssetType::JSLATE, 'Resources/mermaid/mermaid.min.js?v=1.0.0');
        $head->addAsset(AssetType::JSLATE, 'Modules/Workflow/Controller.js?v=1.0.0', ['type' => 'module']);

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
    public function viewWorkflowTemplateCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-template-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response);

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
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-dashboard');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response);

        $instances = WorkflowInstanceAbstractMapper::getAll()
            ->execute();

        $view->data['instances'] = $instances;

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
    public function viewInstanceList(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-instance-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response);

        /** @var \Modules\Workflow\Models\WorkflowInstanceAbstract $instances */
        $instances = WorkflowInstanceAbstractMapper::getAll()
            ->execute();

        $view->data['instances'] = $instances;

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
    public function viewInstance(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view              = new View($this->app->l11nManager, $request, $response);
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response);

        /** @var \Modules\Workflow\Models\WorkflowInstanceAbstract $instance */
        $instance = WorkflowInstanceAbstractMapper::get()
            ->where('id', (int) $request->getData('id'))
            ->execute();

        /** @var \Modules\Workflow\Models\WorkflowTemplate $template */
        $template = WorkflowTemplateMapper::get()
            ->with('source')
            ->with('source/sources')
            ->where('id',  $instance->template->id)
            ->limit()
            ->execute();

        $view->data['template'] = $template;

        if ($template->source->findFile('instance-profile.tpl.php')->id > 0) {
            require_once $template->source->findFile('WorkflowController.php')->getPath();

            /** @var WorkflowControllerInterface $controller */
            $controller = new \Modules\Workflow\Controller\WorkflowController($this->app, $template);
            $controller->createInstanceViewFromRequest($view, $request, $response);
        } else {
            $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-instance');
        }

        return $view;
    }
}
