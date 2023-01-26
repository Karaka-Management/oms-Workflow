<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Admin;

use phpOMS\Application\ApplicationAbstract;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\System\File\PathException;
use phpOMS\Uri\HttpUri;

/**
 * Installer class.
 *
 * @package Modules\Workflow\Admin
 * @license OMS License 1.0
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
     * Install data from providing modules.
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Additional data
     *
     * @return array
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

        $apiApp = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $apiApp->dbPool         = $app->dbPool;
        $apiApp->unitId          = $app->unitId;
        $apiApp->accountManager = $app->accountManager;
        $apiApp->appSettings    = $app->appSettings;
        $apiApp->moduleManager  = $app->moduleManager;
        $apiApp->eventManager   = $app->eventManager;

        foreach ($workflowData as $workflow) {
            self::installWorkflow($apiApp, $workflow);
        }

        return [];
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

        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('name', $data['name']);

        $tempPath = __DIR__ . '/../../../temp/';

        $workflowFiles = \scandir(__DIR__ . '/../../..' . $data['path']);
        if ($workflowFiles === false) {
            return;
        }

        foreach ($workflowFiles as $filePath) {
            if (!\is_file(__DIR__ . '/../../..' . $data['path'] . '/' . $filePath) || $filePath === '..' || $filePath === '.') {
                continue;
            }

            \copy(
                __DIR__ . '/../../..' . $data['path'] . '/' . $filePath,
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
