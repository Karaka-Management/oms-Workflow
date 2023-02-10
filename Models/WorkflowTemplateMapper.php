<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Models;

use Modules\Admin\Models\AccountMapper;
use Modules\Media\Models\CollectionMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class WorkflowTemplateMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'workflow_template_id'                => ['name' => 'workflow_template_id',         'type' => 'int',      'internal' => 'id'],
        'workflow_template_status'            => ['name' => 'workflow_template_status',     'type' => 'int',      'internal' => 'status'],
        'workflow_template_name'              => ['name' => 'workflow_template_name',       'type' => 'string',   'internal' => 'name'],
        'workflow_template_desc'              => ['name' => 'workflow_template_desc',       'type' => 'string',   'internal' => 'description'],
        'workflow_template_descRaw'           => ['name' => 'workflow_template_descRaw',    'type' => 'string',   'internal' => 'descriptionRaw'],
        'workflow_template_schema'           => ['name' => 'workflow_template_schema',    'type' => 'Json',   'internal' => 'schema'],
        'workflow_template_media'             => ['name' => 'workflow_template_media',      'type' => 'int',   'internal' => 'source'],
        'workflow_template_created_at'        => ['name' => 'workflow_template_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'workflow_template_created_by'        => ['name' => 'workflow_template_created_by', 'type' => 'int', 'internal' => 'createdBy', 'readonly' => true],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'source' => [
            'mapper'     => CollectionMapper::class,
            'external'   => 'workflow_template_media',
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
            'mapper'     => AccountMapper::class,
            'external'   => 'workflow_template_created_by',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'workflow_template';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'workflow_template_created_at';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='workflow_template_id';
}
