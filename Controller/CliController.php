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

use Modules\Workflow\Models\WorkflowStatus;
use Modules\Workflow\Models\WorkflowTemplate;
use Modules\Workflow\Models\WorkflowTemplateMapper;
use phpOMS\System\SystemUtils;

/**
 * Workflow controller class.
 *
 * @package Modules\Workflow
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class CliController extends Controller
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
     */
    public function findWorkflow(...$data) : void
    {
        $workflows = WorkflowTemplateMapper::getAll()->where('status', WorkflowStatus::ACTIVE)->execute();
        foreach ($workflows as $workflow) {
            $hooks = $workflow->getHooks();

            foreach ($hooks as $hook) {
                $triggerIsRegex = \stripos($data[':triggerGroup'], '/') === 0;
                $matched = false;

                if ($triggerIsRegex) {
                    $matched = \preg_match($data[':triggerGroup'], $hook) === 1;
                } else {
                    $matched = $data[':triggerGroup'] === $hook;
                }

                if (!$matched && \stripos($hook, '/') === 0) {
                    $matched = \preg_match($hook, $data[':triggerGroup']) === 1;
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
     * @param mixed $data Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function runWorkflow(WorkflowTemplate $workflow, string $hook, array $data) : void
    {
        include $workflow->media->getAbsolutePath();
    }
}
