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

/**
 * Workflow instance class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
class WorkflowInstanceAbstract implements \JsonSerializable
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
     * Instance data.
     *
     * @var int
     * @since 1.0.0
     */
    public int $data_int = 0;

    /**
     * Reference.
     *
     * @var int
     * @since 1.0.0
     */
    public int $ref = 0;

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
     * Workflow steps.
     *
     * @var WorkflowStep[]
     * @since 1.0.0
     */
    public array $steps = [];

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
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'createdAt' => $this->createdAt,
            'data'      => $this->data,
            'data_int'  => $this->data_int,
            'ref'       => $this->ref,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
