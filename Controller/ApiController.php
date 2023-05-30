<?php
/**
 * Karaka
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

namespace Modules\Workflow\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\PathSettings;
use Modules\Workflow\Models\PermissionCategory;
use Modules\Workflow\Models\WorkflowInstanceAbstract;
use Modules\Workflow\Models\WorkflowInstanceAbstractMapper;
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
use phpOMS\System\File\FileUtils;
use phpOMS\System\MimeType;
use phpOMS\Utils\Parser\Markdown\Markdown;
use phpOMS\Utils\StringUtils;
use phpOMS\Utils\TaskSchedule\SchedulerAbstract;
use phpOMS\Utils\TaskSchedule\SchedulerFactory;
use phpOMS\Utils\TaskSchedule\TaskFactory;
use phpOMS\Views\View;

/**
 * Workflow controller class.
 *
 * @package Modules\Workflow
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
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
            $response->data['export'] = new FormValidation($val);
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
                PermissionType::READ, $this->app->unitId, null, self::NAME, PermissionCategory::INSTANCE, $instance->id
            )
            || ($isExport && !$this->app->accountManager->get($accountId)->hasPermission(
                    PermissionType::READ, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::EXPORT
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
        if (($val['id'] = !$request->hasData('id'))) {
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
        /** @var array{lang?:\Modules\Media\Models\Media, cfg?:\Modules\Media\Models\Media, excel?:\Modules\Media\Models\Media, word?:\Modules\Media\Models\Media, powerpoint?:\Modules\Media\Models\Media, pdf?:\Modules\Media\Models\Media, csv?:\Modules\Media\Models\Media, json?:\Modules\Media\Models\Media, template?:\Modules\Media\Models\Media, css?:array<string, \Modules\Media\Models\Media>, js?:array<string, \Modules\Media\Models\Media>, db?:array<string, \Modules\Media\Models\Media>, other?:array<string, \Modules\Media\Models\Media>} $tcoll */
        $tcoll = $view->getData('tcoll') ?? [];

        switch ($request->getData('type')) {
            case 'pdf':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_PDF, true);
                $view->setTemplate('/' . \substr($tcoll['pdf']->getPath(), 0, -8), 'pdf.php');
                break;
            case 'csv':
                $response->header->set(
                    'Content-disposition', 'attachment; filename="'
                    . $name . '.'
                    . ((string) $request->getData('type'))
                    . '"'
                , true);
                $response->header->set('Content-Type', MimeType::M_CONF, true);
                $view->setTemplate('/' . \substr($tcoll['csv']->getPath(), 0, -8), 'csv.php');
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
                $view->setTemplate('/' . \substr($tcoll['excel']->getPath(), 0, -8), 'xls.php');
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
                $view->setTemplate('/' . \substr($tcoll['word']->getPath(), 0, -8), 'doc.php');
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
                $view->setTemplate('/' . \substr($tcoll['powerpoint']->getPath(), 0, -8), 'ppt.php');
                break;
            case 'json':
                $response->header->set('Content-Type', MimeType::M_JSON, true);
                $view->setTemplate('/' . \substr($tcoll['json']->getPath(), 0, -9), 'json.php');
                break;
            default:
                $response->header->set('Content-Type', 'text/html; charset=utf-8');
                $view->setTemplate('/' . \substr($tcoll['template']->getPath(), 0, -8));
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
        /** @var array{lang?:\Modules\Media\Models\Media, cfg?:\Modules\Media\Models\Media, excel?:\Modules\Media\Models\Media, word?:\Modules\Media\Models\Media, powerpoint?:\Modules\Media\Models\Media, pdf?:\Modules\Media\Models\Media, csv?:\Modules\Media\Models\Media, json?:\Modules\Media\Models\Media, template?:\Modules\Media\Models\Media, css?:array<string, \Modules\Media\Models\Media>, js?:array<string, \Modules\Media\Models\Media>, db?:array<string, \Modules\Media\Models\Media>, other?:array<string, \Modules\Media\Models\Media>} $tcoll */
        $tcoll = [];

        /** @var \Modules\Media\Models\Media[] $files */
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
                    if (!isset($tcoll['js'])) {
                        $tcoll['js'] = [];
                    }

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
        $uploadedFiles = $request->files;
        $files         = [];

        if (!empty($val = $this->validateTemplateCreate($request))) {
            $response->data['template_create'] = new FormValidation($val);
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        // is allowed to create
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::CREATE, $this->app->unitId, null, self::NAME, PermissionCategory::TEMPLATE)
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $collectionId = 0;
        $uploaded     = [];

        if ($request->hasFiles()) {
            $path = '/Modules/Workflow/' . $request->getData('name');

            /** @var \Modules\Media\Models\Media[] $uploaded */
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                names: $request->getDataList('names'),
                fileNames: $request->getDataList('filenames'),
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH
            );

            foreach ($uploaded as $upload) {
                if ($upload->id === 0) {
                    continue;
                }

                $files[] = $upload;
            }

            /** @var \Modules\Media\Models\Collection $collection */
            $collection = $this->app->moduleManager->get('Media')->createMediaCollectionFromMedia(
                $request->getDataString('name') ?? '',
                $request->getDataString('description') ?? '',
                $files,
                $request->header->account
            );

            if ($collection->id === 0) {
                $response->header->status = RequestStatusCode::R_403;
                $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Template', 'Couldn\'t create collection for template', null);

                return;
            }

            $collection->setPath('/Modules/Media/Files/Modules/Workflow/' . ($request->getDataString('name') ?? ''));
            $collection->setVirtualPath('/Modules/Workflow');

            $this->createModel($request->header->account, $collection, CollectionMapper::class, 'collection', $request->getOrigin());

            $collectionId = $collection->id;
        }

        $template = $this->createTemplateFromRequest($request, $collectionId);
        $this->createModel($request->header->account, $template, WorkflowTemplateMapper::class, 'workflow_template', $request->getOrigin());

        // replace placeholders
        if ($collectionId > 0) {
            foreach ($uploaded as $upload) {
                if ($upload->id === 0) {
                    continue;
                }

                $path    = $upload->getAbsolutePath();
                $content = \file_get_contents($path);
                if ($content === false) {
                    $content = '';
                }

                $content = $this->parseKeys($content, $template);
                \file_put_contents($path, $content);
            }

            $this->createDatabaseForTemplate($template);
        }

        // perform other workflow installation actions
        $actions = \json_decode(\file_get_contents(__DIR__ . '/../Definitions/actions.json'), true);
        $this->installWorkflowModel($template, $actions);

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Template', 'Template successfully created', $template);
    }

    /**
     * Install a new workflow template.
     *
     * Some actions have a install function, which must be called when first installing/registering a new action in a template.
     * An example could be the cron job for a timed trigger, which must be installed when the template is installed.
     *
     * @param WorkflowTemplate $template Workflow template
     * @param array            $actions  Actions
     *
     * @return void
     *
     * @since 1.0.0
     * @todo: also implement the delete and update functions
     */
    private function installWorkflowModel(WorkflowTemplate $template, array $actions) : void
    {
        $schema = $template->schema;
        foreach ($schema as $primary) {
            $id = $primary['id'] ?? '';

            if (!isset($actions[$id]['function_install'])) {
                continue;
            }

            $this->app->moduleManager->get($actions[$id]['function_install']['module'])->{$actions[$id]['function_install']['function']}($template, $primary);
        }
    }

    /**
     * Installs a timed trigger for a workflow template.
     *
     * @param WorkflowTemplate $template Workflow template
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function installTimedTrigger(WorkflowTemplate $template, array $settings) : void
    {
        SchedulerAbstract::guessBin();

        $id        = 'Workflow-' . $template->id;
        $scheduler = SchedulerFactory::create();

        if (!empty($scheduler->getAllByName($id))) {
            return;
        }

        $job = TaskFactory::create($id);

        $job->interval = $settings['settings']['interval'] ?? '';
        $job->command  = 'php '
            . FileUtils::absolute(__DIR__ . '/../../../cli.php')
            . ' /workflow/instance -id '
            . $template->id
            . ' -trigger 1005500005';

        $scheduler->create($job);
        $scheduler->reload();
    }

    /**
     * Parse and replace placeholder elements
     *
     * @param string           $content  Cotnent to replace
     * @param WorkflowTemplate $template Tempalate model
     *
     * @return string
     *
     * @sicne 1.0.0
     */
    private function parseKeys(string $content, WorkflowTemplate $template) : string
    {
        if ($content === '') {
            return '';
        }

        $content = \str_replace('{workflow_id}', (string) $template->id, $content);

        return $content;
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
        if (($val['name'] = !$request->hasData('name'))) {
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
        $workflowTemplate->name           = $request->getDataString('name') ?? '';
        $workflowTemplate->description    = Markdown::parse($request->getDataString('description') ?? '');
        $workflowTemplate->descriptionRaw = $request->getDataString('description') ?? '';
        $workflowTemplate->schema         = $request->getDataJson('schema');

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
            ->where('id', $template->source->id)
            ->execute();

        $files = $collection->getSources();
        foreach ($files as $file) {
            if (!StringUtils::endsWith($file->getAbsolutePath(), 'db.json')) {
                continue;
            }

            if (!\is_file($file->getAbsolutePath())) {
                return;
            }

            $content = \file_get_contents($file->getAbsolutePath());
            if ($content === false) {
                return; // @codeCoverageIgnore
            }

            $definitions = \json_decode($content, true);
            if ($definitions === false || $definitions === null) {
                return; // @codeCoverageIgnore
            }

            /** @var array $definitions */
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
            $response->data['instance_create'] = new FormValidation($val);
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
        if (($val['template'] = !$request->hasData('template'))) {
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

        // @todo: implement default workflow instance;

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
