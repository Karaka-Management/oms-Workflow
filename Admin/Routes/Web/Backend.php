<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Workflow\Controller\BackendController;
use Modules\Workflow\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^/workflow/template/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplateList',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^/workflow/template/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplate',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^/workflow/template/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplateCreate',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^/workflow/instance/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewInstanceList',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::WORKFLOW,
            ],
        ],
    ],
    '^/workflow/instance/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewInstance',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::WORKFLOW,
            ],
        ],
    ],
];
