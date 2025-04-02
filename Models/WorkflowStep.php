<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\Collection;
use Modules\Media\Models\NullCollection;

/**
 * Workflow template class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
class WorkflowStep
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Order.
     *
     * @var int
     * @since 1.0.0
     */
    public int $order = 0;

    /**
     * Comment.
     *
     * @var string
     * @since 1.0.0
     */
    public string $comment = '';

    /**
     * Data.
     *
     * @var string
     * @since 1.0.0
     */
    public string $data = '';

    /**
     * Status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = 0;

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
     * Instance.
     *
     * @var WorkflowInstanceAbstract
     * @since 1.0.0
     */
    public WorkflowInstanceAbstract $instance;

    /**
     * Media.
     *
     * @var null|Collection
     * @since 1.0.0
     */
    public ?Collection $media = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->instance  = new NullWorkflowInstanceAbstract();
    }

    /**
     * Get hooks
     *
     * @return array
     * @since 1.0.0
     */
    public function getHooks() : array
    {
        return [];
    }
}
