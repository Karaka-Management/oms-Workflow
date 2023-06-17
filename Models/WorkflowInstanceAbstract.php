<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;

/**
 * Workflow instance class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class WorkflowInstanceAbstract
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Title.
     *
     * @var string
     * @since 1.0.0
     */
    public string $title = '';

    /**
     * Instance data.
     *
     * @var string
     * @since 1.0.0
     */
    public string $data = '';

    /**
     * Instance status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = WorkflowInstanceStatus::WORKING;

    /**
     * Template.
     *
     * @var WorkflowTemplate
     * @since 1.0.0
     */
    public WorkflowTemplate $template;

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Created.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * End.
     *
     * @var null|\DateTimeImmutable
     * @since 1.0.0
     */
    public ?\DateTimeImmutable $end = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->template  = new NullWorkflowTemplate();
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
    }

    /**
     * Set status
     *
     * @param int $status Status
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getStatus() : int
    {
        return $this->status;
    }
}
