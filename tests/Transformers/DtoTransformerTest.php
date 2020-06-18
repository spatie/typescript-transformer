<?php

namespace Spatie\TypescriptTransformer\Tests\Transformers;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\NestedDto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\TypeDto;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;

class DtoTransformerTest extends TestCase
{
    private DtoTransformer $transformer;

    protected function setUp() : void
    {
        parent::setUp();

        $this->transformer = new DtoTransformer();
    }

    /** @test */
    public function it_can_transform_types()
    {
//        $this->markTestIncomplete();

        [
            'transformed' => $transformed,
            'missingSymbols' => $missingSymbols,
        ] = $this->transformer->execute(
            new ReflectionClass(TypeDto::class),
            'Typed'
        );

        dd($transformed);
    }

    /** @test */
    public function it_can_transform_nested_types()
    {
        $this->markTestIncomplete();

        $transformed = $this->transformer->execute(
            new ReflectionClass(NestedDto::class),
            'Nested'
        );

        dd($transformed);
    }
}
