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

use Modules\Admin\Models\AccountMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class WorkflowInstanceAbstractMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'workflow_instance_id'         => ['name' => 'workflow_instance_id',         'type' => 'int',      'internal' => 'id'],
        'workflow_instance_title'      => ['name' => 'workflow_instance_title',       'type' => 'string',   'internal' => 'title'],
        'workflow_instance_status'     => ['name' => 'workflow_instance_status',      'type' => 'int',      'internal' => 'status'],
        'workflow_instance_data'       => ['name' => 'workflow_instance_data',      'type' => 'string',      'internal' => 'data'],
        'workflow_instance_template'   => ['name' => 'workflow_instance_template',         'type' => 'int',               'internal' => 'template'],
        'workflow_instance_created_at' => ['name' => 'workflow_instance_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'workflow_instance_created_by' => ['name' => 'workflow_instance_created_by', 'type' => 'int', 'internal' => 'createdBy', 'readonly' => true],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'createdBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'workflow_instance_created_by',
        ],
        'template' => [
            'mapper'   => WorkflowTemplateMapper::class,
            'external' => 'workflow_instance_template',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'workflow_instance';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'workflow_instance_created_at';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'workflow_instance_id';
}
