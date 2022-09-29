<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
use Modules\Workflow\Models\PermissionCategory;
use Modules\Workflow\Models\WorkflowInstanceAbstract;
use Modules\Workflow\Models\WorkflowInstanceAbstractMapper;
use Modules\Workflow\Models\WorkflowStatus;
use Modules\Workflow\Models\WorkflowTemplate;
use Modules\Workflow\Models\WorkflowTemplateMapper;
use phpOMS\Account\PermissionType;
use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\Schema\Builder as SchemaBuilder;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\System\MimeType;
use phpOMS\Utils\Parser\Markdown\Markdown;
use phpOMS\Utils\StringUtils;
use phpOMS\Views\View;

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
     * @param mixed ...$data Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflowFromHook(mixed ...$data) : void
    {
        /** @var WorkflowTemplate[] $workflows */
        $workflows = WorkflowTemplateMapper::getAll()
            ->with('source')
            ->with('source/sources')
            ->where('status', WorkflowStatus::ACTIVE)
            ->execute();

        foreach ($workflows as $workflow) {
            $hooksFile = $workflow->source->findFile('Hooks.php');

            if ($hooksFile instanceof NullMedia) {
                continue;
            }

            $hooksContent = \file_get_contents($hooksFile->getAbsolutePath());
            $hooks        = \json_decode($hooksContent);

            if ($hooks === false || $hooks === null) {
                continue;
            }

            foreach ($hooks as $hook) {
                /** @var array{:triggerGroup?:string} $data */
                $triggerIsRegex = \stripos($data['@triggerGroup'], '/') === 0;
                $matched        = false;

                if ($triggerIsRegex) {
                    $matched = \preg_match($data['@triggerGroup'], $hook) === 1;
                } else {
                    $matched = $data['@triggerGroup'] === $hook;
                }

                if (!$matched && \stripos($hook, '/') === 0) {
                    $matched = \preg_match($hook, $data['@triggerGroup']) === 1;
                }

                if ($matched) {
                    $this->runWorkflow($workflow, $hook, $data);
                }
            }
        }
    }

    /**
     * Api method to make a call to the cli app
     *
     * @param WorkflowTemplate $workflow Workflow template
     * @param string           $hook     Event hook
     * @param array            $data     Event data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflow(WorkflowTemplate $workflow, string $hook, array $data) : void
    {
        include $workflow->source->getAbsolutePath();
    }

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
    public function apiWorkflowExport(HttpRequest $request, HttpResponse $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateExport($request))) {
            $response->set('export', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var WorkflowInstanceAbstract $instance */
        $instance = WorkflowInstanceAbstractMapper::get()
            ->with('template')
            ->with('template/source')
            ->with('template/source/sources')
            ->with('createdBy')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $accountId = $request->header->account;
        $isExport  = \in_array($request->getData('type'), ['xlsx', 'pdf', 'docx', 'pptx', 'csv', 'json']);

        // is allowed to read
        if (!$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, null, self::NAME, PermissionCategory::INSTANCE, $instance->getId()
            )
            || ($isExport && !$this->app->accountManager->get($accountId)->hasPermission(
                    PermissionType::READ, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::EXPORT
            ))
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        if ($isExport) {
            Autoloader::addPath(__DIR__ . '/../../../Resources/');
            $response->header->setDownloadable($instance->template->name, (string) $request->getData('type'));
        }

        $view = $this->createView($instance, $request, $response);
        $this->setWorkflowResponseHeader($view, $instance->template->name, $request, $response);
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
    private function setWorkflowResponseHeader(View $view, string $name, RequestAbstract $request, ResponseAbstract $response) : void
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
     * Create view from template/instance
     *
     * @param WorkflowInstanceAbstract $instance Instance to create view from
     * @param RequestAbstract          $request  Request
     * @param ResponseAbstract         $response Response
     *
     * @return View
     *
     * @api
     *
     * @since 1.0.0
     */
    private function createView(
        WorkflowInstanceAbstract $instance,
        RequestAbstract $request,
        ResponseAbstract $response
    ) : View {
        $tcoll = [];
        $files = $instance->template->source->getSources();

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

        $view->addData('tcoll', $tcoll);
        $view->addData('lang', $request->getData('lang') ?? $request->getLanguage());
        $view->addData('instance', $instance);
        $view->addData('template', $instance->template);
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
    public function apiWorkflowTemplateCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $uploadedFiles = $request->getFiles();
        $files         = [];

        if (!empty($val = $this->validateTemplateCreate($request))) {
            $response->set('template_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::CREATE, $this->app->orgId, null, self::NAME, PermissionCategory::TEMPLATE)
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $path = '/Modules/Workflow/' . $request->getData('name');

        /** @var \Modules\Media\Models\Media[] $uploaded */
        $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
            $request->getDataList('names'),
            $request->getDataList('filenames'),
            $uploadedFiles,
            $request->header->account,
            __DIR__ . '/../../../Modules/Media/Files' . $path,
            $path,
            pathSettings: PathSettings::FILE_PATH
        );

        foreach ($uploaded as $upload) {
            if ($upload instanceof NullMedia) {
                continue;
            }

            $files[] = $upload;
        }

        /** @var \Modules\Media\Models\Collection $collection */
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

        $this->createModel($request->header->account, $template, WorkflowTemplateMapper::class, 'template', $request->getOrigin());

        // replace placeholders
        foreach ($uploaded as $upload) {
            if ($upload instanceof NullMedia) {
                continue;
            }

            $path    = $upload->getAbsolutePath();
            $content = \file_get_contents($path);
            if ($content === false) {
                $content = '';
            }

            $content = \str_replace('{workflow_id}', (string) $template->getId(), $content);
            \file_put_contents($path, $content);
        }

        $this->createDatabaseForTemplate($template);

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Template', 'Template successfully created', $template);
    }

    /**
     * Create media directory path
     *
     * @param WorkflowTemplate $template Workflow template
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createTemplateDir(WorkflowTemplate $template) : string
    {
        return '/Modules/Workflow/'
            . $template->getId() . ' '
            . $template->name;
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
            || ($val['files'] = empty($request->getFiles()))
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
     * @return WorkflowTemplate
     *
     * @since 1.0.0
     */
    private function createTemplateFromRequest(RequestAbstract $request, int $collectionId) : WorkflowTemplate
    {
        $workflowTemplate                 = new WorkflowTemplate();
        $workflowTemplate->name           = $request->getData('name') ?? '';
        $workflowTemplate->description    = Markdown::parse((string) ($request->getData('description') ?? ''));
        $workflowTemplate->descriptionRaw = (string) ($request->getData('description') ?? '');

        if ($collectionId > 0) {
            $workflowTemplate->source = new NullCollection($collectionId);
        }

        $workflowTemplate->createdBy = new NullAccount($request->header->account);

        return $workflowTemplate;
    }

    /**
     * Method to create database for template.
     *
     * @param WorkflowTemplate $template Workflow template
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createDatabaseForTemplate(WorkflowTemplate $template) : void
    {
        /** @var \Modules\Media\Models\Collection $collection */
        $collection = CollectionMapper::get()
            ->with('sources')
            ->where('id', $template->source->getId())
            ->execute();

        $files = $collection->getSources();
        foreach ($files as $file) {
            if (!StringUtils::endsWith($file->getPath(), 'db.json')) {
                continue;
            }

            if (!\is_file($file->getPath())) {
                return;
            }

            $content = \file_get_contents($file->getPath());
            if ($content === false) {
                return; // @codeCoverageIgnore
            }

            $definitions = \json_decode($content, true);
            if ($definitions === false || $definitions === null) {
                return; // @codeCoverageIgnore
            }

            foreach ($definitions as $definition) {
                SchemaBuilder::createFromSchema($definition, $this->app->dbPool->get('schema'))->execute();
            }

            return;
        }
    }

    /**
     * Method which creates a workflow instance
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function apiWorkflowInstanceCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateInstanceCreate($request))) {
            $response->set('instance_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Workflow\Models\WorkflowTemplate $template */
        $template = WorkflowTemplateMapper::get()
            ->with('source')
            ->with('source/sources')
            ->where('id', (int) $request->getData('template'))
            ->execute();

        $instance = $this->createInstanceFromRequest($request, $template);

        $this->createModel(
            $request->header->account,
            $instance,
            WorkflowInstanceAbstractMapper::class,
            'instance',
            $request->getOrigin()
        );
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Instance', 'Instance successfully created', $instance);
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
    private function validateInstanceCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['template'] = empty($request->getData('template')))) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create interface from request.
     *
     * @param RequestAbstract  $request  Request
     * @param WorkflowTemplate $template Workflow template
     *
     * @return WorkflowInstanceAbstract
     *
     * @since 1.0.0
     */
    private function createInstanceFromRequest(RequestAbstract $request, WorkflowTemplate $template) : WorkflowInstanceAbstract
    {
        $controller = null;

        $file = $template->source->findFile('WorkflowController.php');
        require_once $file->getAbsolutePath();

        /** @var \Modules\Workflow\Models\WorkflowControllerInterface $controller */
        $controller = new \Modules\Workflow\Controller\WorkflowController($this->app, $template);

        $instance = $controller->createInstanceFromRequest($request, $template);

        return $instance;
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
    public function apiWorkflowImport(HttpRequest $request, HttpResponse $response, mixed $data = null) : void
    {
    }
}
