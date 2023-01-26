<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Admin\Install;

use phpOMS\Application\ApplicationAbstract;

/**
 * Workflow class.
 *
 * @package Modules\Workflow\Admin\Install
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Workflow
{
    /**
     * Install workflow providing
     *
     * @param ApplicationAbstract $app  Application
     * @param string              $path Module path
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(ApplicationAbstract $app, string $path) : void
    {
        \Modules\Workflow\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Workflow.install.json']);
    }
}
