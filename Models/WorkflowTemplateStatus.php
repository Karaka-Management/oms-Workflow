<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Bill status enum.
 *
 * @package Modules\Workflow\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class WorkflowTemplateStatus extends Enum
{
    public const ACTIVE = 1;

    public const INACTIVE = 2;
}
