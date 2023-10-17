<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\tests\Models;

use Modules\Workflow\Models\NullWorkflowInstanceAbstract;

/**
 * @internal
 */
final class NullWorkflowInstanceAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Workflow\Models\NullWorkflowInstanceAbstract
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Workflow\Models\WorkflowInstanceAbstract', new NullWorkflowInstanceAbstract());
    }

    /**
     * @covers Modules\Workflow\Models\NullWorkflowInstanceAbstract
     * @group module
     */
    public function testId() : void
    {
        $null = new NullWorkflowInstanceAbstract(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Workflow\Models\NullWorkflowInstanceAbstract
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullWorkflowInstanceAbstract(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
