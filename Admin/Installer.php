<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Admin;

use Modules\Workflow\Admin\Install\Workflow;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;
use phpOMS\System\File\PathException;

/**
 * Installer class.
 *
 * @package Modules\Workflow\Admin
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * Path of the file
     *
     * @var string
     * @since 1.0.0
     */
    public const PATH = __DIR__;

    /**
     * {@inheritdoc}
     */
    public static function install(ApplicationAbstract $app, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($app, $info, $cfgHandler);
        Workflow::install($app, '');
    }

    /**
     * Install data from providing modules.
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Additional data
     *
     * @return array
     *
     * @throws PathException
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public static function installExternal(ApplicationAbstract $app, array $data) : array
    {
        if (!\is_file($data['path'] ?? '')) {
            throw new PathException($data['path'] ?? '');
        }

        $workflowFile = \file_get_contents($data['path'] ?? '');
        if ($workflowFile === false) {
            throw new PathException($data['path'] ?? ''); // @codeCoverageIgnore
        }

        $workflowData = \json_decode($workflowFile, true) ?? [];
        if (!\is_array($workflowData)) {
            throw new \Exception(); // @codeCoverageIgnore
        }

        if (!\is_dir(__DIR__ . '/../../../temp')) {
            \mkdir(__DIR__ . '/../../../temp');
        }

        if (!\is_dir(__DIR__ . '/../Definitions')) {
            \mkdir(__DIR__ . '/../Definitions');

            \file_put_contents(__DIR__ . '/../Definitions/actions.json', '[]');
            \file_put_contents(__DIR__ . '/../Definitions/triggers.json', '[]');
        }

        $apiApp = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $apiApp->dbPool         = $app->dbPool;
        $apiApp->unitId         = $app->unitId;
        $apiApp->accountManager = $app->accountManager;
        $apiApp->appSettings    = $app->appSettings;
        $apiApp->moduleManager  = $app->moduleManager;
        $apiApp->eventManager   = $app->eventManager;

        self::createTriggers($apiApp, $workflowData['triggers'] ?? []);
        self::createActions($apiApp, $workflowData['actions'] ?? []);
        self::createWorkflows($apiApp, $workflowData['workflows'] ?? []);
        self::installWorkflow($apiApp, $workflowData['templates'] ?? []);

        return [];
    }

    /**
     * Create a workflow template
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Workflow schemas
     *
     * @return void
     *
     * @since 1.0.0
     */
    private static function createWorkflows(ApplicationAbstract $app, array $data) : void
    {
        /** @var \Modules\Workflow\Controller\ApiController $module */
        $module = $app->moduleManager->get('Workflow');

        foreach ($data as $name => $workflow) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $name);
            $request->setData('schema', \json_encode($workflow));

            $module->apiWorkflowTemplateCreate($request, $response);
        }
    }

    /**
     * Install trigger.
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Additional data
     *
     * @return void
     *
     * @since 1.0.0
     */
    private static function createTriggers(ApplicationAbstract $app, array $data) : void
    {
        $path = __DIR__ . '/../Definitions/triggers.json';
        if (!\is_file($path)) {
            return;
        }

        $installed = \file_get_contents($path);
        if ($installed === false) {
            return;
        }

        $installedData = \json_decode($installed, true);
        if ($installedData === false) {
            return;
        }

        $new = $installedData + $data;

        \file_put_contents($path, \json_encode($new));
    }

    /**
     * Install action.
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Additional data
     *
     * @return void
     *
     * @since 1.0.0
     */
    private static function createActions(ApplicationAbstract $app, array $data) : void
    {
        $path = __DIR__ . '/../Definitions/actions.json';
        if (!\is_file($path)) {
            return;
        }

        $installed = \file_get_contents($path);
        if ($installed === false) {
            return;
        }

        $installedData = \json_decode($installed, true);
        if ($installedData === false) {
            return;
        }

        $new = $installedData + $data;

        \file_put_contents($path, \json_encode($new));
    }

    /**
     * Install application page.
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Additional data
     *
     * @return void
     *
     * @since 1.0.0
     */
    private static function installWorkflow(ApplicationAbstract $app, array $data) : void
    {
        /** @var \Modules\Workflow\Controller\ApiController $module */
        $module = $app->moduleManager->get('Workflow');

        foreach ($data as $template) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $template['name']);

            $tempPath = __DIR__ . '/../../../temp/';

            $workflowFiles = \scandir(__DIR__ . '/../../..' . $template['path']);
            if ($workflowFiles === false) {
                return;
            }

            foreach ($workflowFiles as $filePath) {
                if (!\is_file(__DIR__ . '/../../..' . $template['path'] . '/' . $filePath) || $filePath === '..' || $filePath === '.') {
                    continue;
                }

                \copy(
                    __DIR__ . '/../../..' . $template['path'] . '/' . $filePath,
                    $tempPath . $filePath
                );

                $request->addFile([
                    'error'    => \UPLOAD_ERR_OK,
                    'type'     => \substr($filePath, \strrpos($filePath, '.') + 1),
                    'name'     => $filePath,
                    'tmp_name' => $tempPath . $filePath,
                    'size'     => \filesize($tempPath . $filePath),
                ]);
            }

            $module->apiWorkflowTemplateCreate($request, $response);
        }
    }
}
