<?php

namespace Spatie\TypescriptTransformer\Tests\ClassPropertyProcessors;

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\ApplyNeverClassPropertyProcessor;
use Spatie\TypescriptTransformer\Support\UnknownType;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeReflectionProperty;

class ApplyNeverClassPropertyProcessorTest extends TestCase
{
    private ApplyNeverClassPropertyProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ApplyNeverClassPropertyProcessor();
    }

    /** @test */
    public function it_replaces_arrays_with_null()
    {
        $type = new Compound([
            new Array_(new Integer()),
            new Array_(new Null_()),
        ]);

        $type = $this->processor->process($type, FakeReflectionProperty::create());


        $this->assertEquals(new Compound([
            new Array_(new Integer()),
            new Array_(new UnknownType()),
        ]), $type);
    }
}
