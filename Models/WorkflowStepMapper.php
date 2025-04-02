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

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\ModuleMapper;
use Modules\Media\Models\CollectionMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * WorkflowStep mapper class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of WorkflowStep
 * @extends DataMapperFactory<T>
 */
final class WorkflowStepMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'workflow_step_id'         => ['name' => 'workflow_step_id',         'type' => 'int',      'internal' => 'id'],
        'workflow_step_status'     => ['name' => 'workflow_step_status',     'type' => 'int',      'internal' => 'status'],
        'workflow_step_order'     => ['name' => 'workflow_step_order',     'type' => 'int',      'internal' => 'order'],
        'workflow_step_type'     => ['name' => 'workflow_step_type',     'type' => 'int',      'internal' => 'type'],
        'workflow_step_comment'       => ['name' => 'workflow_step_comment',       'type' => 'string',   'internal' => 'comment'],
        'workflow_step_data'    => ['name' => 'workflow_step_data',    'type' => 'string',   'internal' => 'data'],
        'workflow_step_instance'     => ['name' => 'workflow_step_instance',    'type' => 'Json',   'internal' => 'instance'],
        'workflow_step_media'      => ['name' => 'workflow_step_media',      'type' => 'int',   'internal' => 'media'],
        'workflow_step_created_at' => ['name' => 'workflow_step_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'workflow_step_created_by' => ['name' => 'workflow_step_created_by', 'type' => 'int', 'internal' => 'createdBy', 'readonly' => true],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'media' => [
            'mapper'   => CollectionMapper::class,
            'external' => 'workflow_step_media',
        ],
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
            'external' => 'workflow_step_created_by',
        ],
        'instance' => [
            'mapper'   => ModuleMapper::class,
            'external' => 'workflow_step_instance',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'workflow_step';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'workflow_step_created_at';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'workflow_step_id';
}
