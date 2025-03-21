<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
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
 * @license OMS License 2.2
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
     * Change workflow instance state based on a hook
     *
     * @param WorkflowTemplate $template Workflow template
     * @param int              $account  Account who created the model
     * @param mixed            $old      Old value
     * @param mixed            $new      New value (unused, should be null)
     * @param int              $type     Module model type
     * @param string           $trigger  What triggered this log?
     * @param string           $module   Module name
     * @param string           $ref      Reference to other model
     * @param string           $content  Message
     * @param string           $ip       Ip
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function hookChangeState(
        WorkflowTemplate $template,
        int $account,
        mixed $old,
        mixed $new,
        ?int $type = null,
        string $trigger = '',
        ?string $module = null,
        ?string $ref = null,
        ?string $content = null,
        ?string $ip = null
    ) : mixed;

    /**
     * Handle workflow instance state
     *
     * This can be used to determine if a certain action is allowed or not based on the current state
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param null|mixed       $data     Data
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function apiHandleState(RequestAbstract $request, ResponseAbstract $response, $data = null) : void;

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
