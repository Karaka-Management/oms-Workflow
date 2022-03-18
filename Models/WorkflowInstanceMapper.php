<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Models;

use Modules\Admin\Models\AccountMapper;
use Modules\Calendar\Models\ScheduleMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Mapper\ReadMapper;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\Query\Where;

/**
 * Mapper class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class WorkflowInstanceMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'workflow_instance_id'                => ['name' => 'workflow_instance_id',         'type' => 'int',      'internal' => 'id'],
        'workflow_instance_template'         => ['name' => 'workflow_instance_template',         'type' => 'int',               'internal' => 'template'],
        'workflow_instance_created_at'        => ['name' => 'workflow_instance_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'workflow_instance_created_by'        => ['name' => 'workflow_instance_created_by', 'type' => 'int', 'internal' => 'createdBy', 'readonly' => true],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string}>
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
    public const PRIMARYFIELD ='workflow_instance_id';
}
