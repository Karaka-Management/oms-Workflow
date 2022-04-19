<?php
/**
 * Karaka
 *
 * PHP Version 8.1
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
use Modules\Workflow\Models\WorkflowControllerInterface;
use Modules\Workflow\Models\WorkflowInstanceMapper;
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
            ->with('source')
            ->with('source/sources')
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->setData('template', $template);

        if (!(($template->source->findFile('template-profile.tpl.php')) instanceof NullMedia)) {
            require_once $template->source->findFile('WorkflowController.php')->getPath();

            /** @var WorkflowControllerInterface $controller */
            $controller = new \Modules\Workflow\Controller\WorkflowController($this->app, $template);
            $controller->createTemplateViewFromRequest($view, $request, $response);
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
        $view->setTemplate('/Modules/Workflow/Theme/Backend/workflow-instance-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005501001, $request, $response));

        $instances = WorkflowInstanceMapper::getAll()
            ->execute();

        $view->setData('instances', $instances);

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
            ->with('source')
            ->with('source/sources')
            ->where('template', (int) $request->getData('template'))
            ->execute();

        if (!(($template->source->findFile('instance-profile.tpl.php')) instanceof NullMedia)) {
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
