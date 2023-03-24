<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Models;

use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Controller interface.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
interface WorkflowControllerInterface
{
    /**
     * Create instance from request
     *
     * @param RequestAbstract  $request  Request
     * @param WorkflowTemplate $template Workflow template
     *
     * @return WorkflowInstanceAbstract
     *
     * @since 1.0.0
     */
    public function createInstanceFromRequest(RequestAbstract $request, WorkflowTemplate $template) : WorkflowInstanceAbstract;

    /**
     * Create list of all instances for this workflow from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getInstanceListFromRequest(RequestAbstract $request) : array;

    /**
     * Change workflow instance state
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param null|mixed       $data     Data
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function apiChangeState(RequestAbstract $request, ResponseAbstract $response, $data = null) : void;

    /**
     * Store instance model in the database
     *
     * @param WorkflowInstanceAbstract $instance Instance
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function createInstanceDbModel(WorkflowInstanceAbstract $instance) : void;

    /**
     * Create view for instance
     *
     * @param View             $view     View to populate
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function createInstanceViewFromRequest(View $view, RequestAbstract $request, ResponseAbstract $response) : void;

    /**
     * Create view for template
     *
     * @param View             $view     View to populate
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function createTemplateViewFromRequest(View $view, RequestAbstract $request, ResponseAbstract $response) : void;
}
