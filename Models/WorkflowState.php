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

use phpOMS\Stdlib\Base\Enum;

/**
 * Workflow state enum.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class WorkflowState extends Enum
{
    public const OPEN = 1;

    public const WORKING = 2;

    public const SUSPENDED = 3;

    public const CANCELED = 4;

    public const DONE = 5;

    public const CLOSED = 6;
}
