<?php

namespace Spatie\TypescriptTransformer\Tests\ClassPropertyProcessors;

use DateTime;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\ReplaceDefaultTypesClassPropertyProcessor;
use Spatie\TypescriptTransformer\Support\TypescriptType;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypescriptTransformer\Tests\Fakes\FakePropertyReflection;

class ReplaceDefaultTypesClassPropertyProcessorTest extends TestCase
{
    private ReplaceDefaultTypesClassPropertyProcessor $processor;

    private TypeResolver $typeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeResolver = new TypeResolver();

        $this->processor = new ReplaceDefaultTypesClassPropertyProcessor([
            DateTime::class => new String_(),
            Dto::class => new TypescriptType('array'),
        ]);
    }

    /** @test */
    public function it_can_replace_types()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(Dto::class),
            FakePropertyReflection::create()
        );

        $this->assertEquals(new TypescriptType('array'), $type);
    }

    /** @test */
    public function it_can_replace_types_as_nullable()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve('?' . DateTime::class),
            FakePropertyReflection::create()
        );

        $this->assertEquals(new Nullable(new String_()), $type);
    }

    /** @test */
    public function it_can_replace_types_in_arrays()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(DateTime::class . '[]'),
            FakePropertyReflection::create()
        );

        $this->assertEquals(new Array_(new String_()), $type);
    }
}
