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

use Modules\Workflow\Models\NullWorkflowTemplate;

/**
 * @internal
 */
final class NullWorkflowTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Workflow\Models\NullWorkflowTemplate
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Workflow\Models\WorkflowTemplate', new NullWorkflowTemplate());
    }

    /**
     * @covers Modules\Workflow\Models\NullWorkflowTemplate
     * @group module
     */
    public function testId() : void
    {
        $null = new NullWorkflowTemplate(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Workflow\Models\NullWorkflowTemplate
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullWorkflowTemplate(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
