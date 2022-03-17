<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use Modules\Workflow\Controller\BackendController;
use Modules\Workflow\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/workflow/template/list.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplates',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/template/single.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/template/create.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowTemplateCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/dashboard.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::WORKFLOW,
            ],
        ],
    ],
    '^.*/workflow/single.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\BackendController:viewWorkflowSingle',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::WORKFLOW,
            ],
        ],
    ],
];
