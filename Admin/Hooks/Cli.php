<?php
/**
 * Jingga
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

return [
    '/.*/' => [
        'callback' => ['\Modules\Workflow\Controller\CliController:runWorkflowFromHook'],
    ],
];
