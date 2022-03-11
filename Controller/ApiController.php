<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Controller;

use phpOMS\System\SystemUtils;

/**
 * Workflow controller class.
 *
 * @package Modules\Workflow
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Api method to make a call to the cli app
     *
     * @param mixed $data Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     * @todo maybe this needs to be moved to the admin module if there every is another hook which uses .* regex-match and is forwarded to the cli application
     */
    public function cliEventCall(...$data) : void
    {
        $count = \count($data);

        // @todo: if no Cli is available do it in the web app (maybe first web request and if this is also not allowed run it in here)
        SystemUtils::runProc(
            'php',
            __DIR__ . '/../../../cli.php' . ' '
                . 'post:/admin/event' . ' '
                . '-g ' . \escapeshellarg($data[$count - 2]) . ' '
                . '-i ' . \escapeshellarg($data[$count - 1]) . ' '
                . '-d ' . \escapeshellarg(\json_encode($data)),
            true
        );
    }
}
