<?php

namespace Spatie\TypescriptTransformer\Tests\ClassPropertyProcessors;

use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\CleanupClassPropertyProcessor;
use Spatie\TypescriptTransformer\Tests\Fakes\FakePropertyReflection;
use Spatie\TypescriptTransformer\ValueObjects\ClassProperty;

class CleanupClassPropertyProcessorTest extends TestCase
{
    private CleanupClassPropertyProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new CleanupClassPropertyProcessor();
    }

    /** @test */
    public function it_will_let_regular_types_pass()
    {
        $property = ClassProperty::create(
            FakePropertyReflection::create(),
            ['array', 'null', 'int', 'bool', 'x'],
            ['array', 'null', 'int', 'bool', 'x'],
        );

        $this->assertEquals(
            $property,
            $this->processor->process(clone $property)
        );
    }

    /** @test */
    public function it_will_remove_array_types()
    {
        $property = ClassProperty::create(
            FakePropertyReflection::create(),
            ['array', 'null', 'int[]', 'bool', 'x'],
            ['array', 'null', 'int', 'bool', 'x'],
        );

        $this->assertEquals(
            ClassProperty::create(
                FakePropertyReflection::create(),
                ['array', 'null', 'bool', 'x'],
                ['array', 'null', 'int', 'bool', 'x'],
            ),
            $this->processor->process(clone $property)
        );
    }
}
