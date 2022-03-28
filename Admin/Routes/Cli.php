<?php declare(strict_types=1);

use phpOMS\Router\RouteVerb;

return [
    '^.*/workflow/instance.*$' => [
        [
            'dest' => '\Modules\Workflow\Controller\CliController:cliWorkflowInstanceCreate',
            'verb' => RouteVerb::PUT,
        ],
    ]
];
