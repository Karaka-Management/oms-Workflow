<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Workflow\Controller\ApiController;
use Modules\Workflow\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/workflow/instance/export.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\ApiController:apiWorkflowExport',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::WORKFLOW,
            ],
        ],
    ],
    '^.*/workflow/template.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\ApiController:apiWorkflowTemplateCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TEMPLATE,
            ],
        ],
    ],
    '^.*/workflow/instance.*$' => [
        [
            'dest'       => '\Modules\Workflow\Controller\ApiController:apiWorkflowInstanceCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::WORKFLOW,
            ],
        ],
    ],
];
