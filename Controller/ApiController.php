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

use Modules\Admin\Models\AccountPermission;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Workflow\Models\PermissionCategory;
use Modules\Workflow\Models\WorkflowTemplate;
use Modules\Workflow\Models\WorkflowTemplateMapper;
use phpOMS\Account\PermissionType;
use phpOMS\Autoloader;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\System\MimeType;
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
     * Routing end-point for application behaviour.
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     * @param mixed        $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiWorkflowExport(HttpRequest $request, HttpResponse $response, $data = null) : void
    {
        if (!empty($val = $this->validateExport($request))) {
            $response->set('export', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var Template $template */
        $template = WorkflowTemplateMapper::get()
            ->with('source')
            ->with('source/sources')
            ->with('reports')
            ->with('reports/source')
            ->with('reports/source/sources')
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $accountId = $request->header->account;
        $isExport  = \in_array($request->getData('type'), ['xlsx', 'pdf', 'docx', 'pptx', 'csv', 'json']);

        // is allowed to read
        if (!$this->app->accountManager->get($accountId)->hasPermission(PermissionType::READ, $this->app->orgId, null, self::NAME, PermissionCategory::REPORT, $template->getId())
            || ($isExport && !$this->app->accountManager->get($accountId)->hasPermission(PermissionType::READ, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::EXPORT))
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        if ($isExport) {
            Autoloader::addPath(__DIR__ . '/../../../Resources/');
            $response->header->setDownloadable($template->name, (string) $request->getData('type'));
        }

        $view = $this->createView($template, $request, $response);
        $this->setHelperResponseHeader($view, $template->name, $request, $response);
        $view->setData('path', __DIR__ . '/../../../');

        $response->set('export', $view);
    }

    /**
     * Validate export request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateExport(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = empty($request->getData('id')))) {
            return $val;
        }

        return [];
    }

    /**
     * Set header for report/template
     *
     * @param View             $view     Template view
     * @param string           $name     Template name
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    private function setHelperResponseHeader(View $view, string $name, RequestAbstract $request, ResponseAbstract $response) : void
    {
        switch ($request->getData('type')) {
            case 'pdf':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_PDF, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['pdf']?->getPath(), 0, -8), 'pdf.php');
                break;
            case 'csv':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_CONF, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['csv']?->getPath(), 0, -8), 'csv.php');
                break;
            case 'xls':
            case 'xlsx':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['excel']?->getPath(), 0, -8), 'xls.php');
                break;
            case 'doc':
            case 'docx':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['word']?->getPath(), 0, -8), 'doc.php');
                break;
            case 'ppt':
            case 'pptx':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_XLSX, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['powerpoint']?->getPath(), 0, -8), 'ppt.php');
                break;
            case 'json':
                $response->header->set('Content-Type', MimeType::M_JSON, true);
                $view->setTemplate('/' . \substr($view->getData('tcoll')['json']?->getPath(), 0, -9), 'json.php');
                break;
            default:
                $response->header->set('Content-Type', 'text/html; charset=utf-8');
                $view->setTemplate('/' . \substr($view->getData('tcoll')['template']?->getPath(), 0, -8));
        }
    }

    /**
     * Create view from template
     *
     * @param Template         $template Template to create view from
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     *
     * @return View
     *
     * @api
     *
     * @since 1.0.0
     */
    private function createView(Template $template, RequestAbstract $request, ResponseAbstract $response) : View
    {
        /** @var array<string, \Modules\Media\Models\Media|\Modules\Media\Models\Media[]> $tcoll */
        $tcoll = [];
        $files = $template->source->getSources();

        /** @var \Modules\Media\Models\Media $tMedia */
        foreach ($files as $tMedia) {
            $lowerPath = \strtolower($tMedia->getPath());

            switch (true) {
                case StringUtils::endsWith($lowerPath, '.lang.php'):
                    $tcoll['lang'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.cfg.json'):
                    $tcoll['cfg'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.xlsx.php'):
                case StringUtils::endsWith($lowerPath, '.xls.php'):
                    $tcoll['excel'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.docx.php'):
                case StringUtils::endsWith($lowerPath, '.doc.php'):
                    $tcoll['word'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.pptx.php'):
                case StringUtils::endsWith($lowerPath, '.ppt.php'):
                    $tcoll['powerpoint'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.pdf.php'):
                    $tcoll['pdf'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.csv.php'):
                    $tcoll['csv'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.json.php'):
                    $tcoll['json'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.tpl.php'):
                    $tcoll['template'] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.css'):
                    if (!isset($tcoll['css'])) {
                        $tcoll['css'] = [];
                    }

                    $tcoll['css'][$tMedia->name] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.js'):
                    $tcoll['js'][$tMedia->name] = $tMedia;
                    break;
                case StringUtils::endsWith($lowerPath, '.sqlite'):
                case StringUtils::endsWith($lowerPath, '.db'):
                    if (!isset($tcoll['db'])) {
                        $tcoll['db'] = [];
                    }

                    $tcoll['db'][$tMedia->name] = $tMedia;
                    break;
                default:
                    if (!isset($tcoll['other'])) {
                        $tcoll['other'] = [];
                    }

                    $tcoll['other'][$tMedia->name] = $tMedia;
            }
        }

        $view = new View($this->app->l11nManager, $request, $response);
        if (!$template->isStandalone) {
            /** @var Report $report */
            $report = ReportMapper::get()
                ->with('template')
                ->with('source')
                ->with('source/sources')
                ->where('template', $template->getId())
                ->sort('id', OrderType::DESC)
                ->limit(1)
                ->execute();

            $rcoll  = [];
            $report = $report === false ? new NullReport() : $report;

            if (!($report instanceof NullReport)) {
                $files = $report->source->getSources();

                foreach ($files as $media) {
                    $rcoll[$media->name . '.' . $media->extension] = $media;
                }
            }

            $view->addData('report', $report);
            $view->addData('rcoll', $rcoll);
        }

        $view->addData('tcoll', $tcoll);
        $view->addData('lang', $request->getData('lang') ?? $request->getLanguage());
        $view->addData('template', $template);
        $view->addData('basepath', __DIR__ . '/../../../');

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTemplateCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $dbFiles       = $request->getDataJson('media-list') ?? [];
        $uploadedFiles = $request->getFiles() ?? [];
        $files         = [];

        if (!empty($val = $this->validateTemplateCreate($request))) {
            $response->set('template_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(PermissionType::CREATE, $this->app->orgId, null, self::NAME, PermissionCategory::TEMPLATE)) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
            $request->getDataList('names') ?? [],
            $request->getDataList('filenames') ?? [],
            $uploadedFiles,
            $request->header->account,
            __DIR__ . '/../../../Modules/Media/Files'
        );

        foreach ($uploaded as $upload) {
            $files[] = new NullMedia($upload->getId());
        }

        foreach ($dbFiles as $db) {
            $files[] = new NullMedia($db);
        }

        /** @var Collection $collection */
        $collection = $this->app->moduleManager->get('Media')->createMediaCollectionFromMedia(
            (string) ($request->getData('name') ?? ''),
            (string) ($request->getData('description') ?? ''),
            $files,
            $request->header->account
        );

        if ($collection instanceof NullCollection) {
            $response->header->status = RequestStatusCode::R_403;
            $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Template', 'Couldn\'t create collection for template', null);

            return;
        }

        $collection->setPath('/Modules/Media/Files/Modules/Workflow/' . ((string) ($request->getData('name') ?? '')));
        $collection->setVirtualPath('/Modules/Workflow');

        CollectionMapper::create()->execute($collection);

        $template = $this->createTemplateFromRequest($request, $collection->getId());

        $this->app->moduleManager->get('Admin')->createAccountModelPermission(
            new AccountPermission(
                $request->header->account,
                $this->app->orgId,
                $this->app->appName,
                self::NAME,
                self::NAME,
                PermissionCategory::TEMPLATE,
                $template->getId(),
                null,
                PermissionType::READ | PermissionType::MODIFY | PermissionType::DELETE | PermissionType::PERMISSION,
            ),
            $request->header->account,
            $request->getOrigin()
        );

        $this->createModel($request->header->account, $template, TemplateMapper::class, 'template', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Template', 'Template successfully created', $template);
    }

    /**
     * Validate template create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTemplateCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['name'] = empty($request->getData('name')))
            || ($val['files'] = empty($request->getFiles() ?? []))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create template from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return Template
     *
     * @since 1.0.0
     */
    private function createTemplateFromRequest(RequestAbstract $request, int $collectionId) : WorkflowTemplate
    {
        $expected = $request->getData('expected');

        $workflowTemplate                 = new WorkflowTemplate();
        $workflowTemplate->name           = $request->getData('name') ?? 'Empty';
        $workflowTemplate->description    = Markdown::parse((string) ($request->getData('description') ?? ''));
        $workflowTemplate->descriptionRaw = (string) ($request->getData('description') ?? '');

        if ($collectionId > 0) {
            $workflowTemplate->source = new NullCollection($collectionId);
        }

        $workflowTemplate->isStandalone = (bool) ($request->getData('standalone') ?? false);
        $workflowTemplate->setExpected(!empty($expected) ? \json_decode($expected, true) : []);
        $workflowTemplate->createdBy = new NullAccount($request->header->account);
        $workflowTemplate->setDatatype((int) ($request->getData('datatype') ?? TemplateDataType::OTHER));
        $workflowTemplate->virtualPath = (string) ($request->getData('virtualpath') ?? '/');

        return $workflowTemplate;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiWorkflowInstanceCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
    }

    /**
     * Import data for Workflow
     *
     * Example: Create multiple workflow instances from excel sheet
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     * @param mixed        $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiWorkflowImport(HttpRequest $request, HttpResponse $response, $data = null) : void
    {
    }

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
