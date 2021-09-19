<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Workflow\Controller\BackendController;
use Modules\Workflow\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/workflow/template/list.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplates',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/template/single.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/template/create.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplateCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/dashboard.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::WORKFLOW,
            ],
        ],
    ],
    '^.*/workflow/single.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowSingle',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::WORKFLOW,
            ],
        ],
    ],
];
