<?php

namespace Spatie\TypescriptTransformer\Tests\ClassPropertyProcessors;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\DtoCollectionClassPropertyProcessor;
use Spatie\TypescriptTransformer\Support\UnknownType;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\DtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\NullableDtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\StringDtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\UntypedDtoCollection;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeReflectionProperty;

class DtoCollectionClassPropertyProcessorTest extends TestCase
{
    private DtoCollectionClassPropertyProcessor $processor;

    private TypeResolver $typeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeResolver = new TypeResolver();

        $this->processor = new DtoCollectionClassPropertyProcessor();
    }

    /** @test */
    public function it_will_process_a_dto_collection()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(DtoCollection::class),
            FakeReflectionProperty::create()
        );

        $this->assertEquals(
            '\Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Dto[]',
            (string) $type
        );
    }

    /** @test */
    public function it_will_process_a_nullable_dto_collection()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(NullableDtoCollection::class),
            FakeReflectionProperty::create()
        );

        $this->assertEquals(
            '?\Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Dto[]',
            (string) $type
        );
    }

    /** @test */
    public function it_will_process_a_dto_collection_with_built_in_type()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(StringDtoCollection::class),
            FakeReflectionProperty::create()
        );

        $this->assertEquals('string[]', (string) $type);
    }

    /** @test */
    public function it_will_process_a_dto_collection_without_type()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(UntypedDtoCollection::class),
            FakeReflectionProperty::create()
        );

        $this->assertEquals(new Array_(new UnknownType()), $type);
    }

    /** @test */
    public function it_will_pass_non_dto_collections()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve('string'),
            FakeReflectionProperty::create()
        );

        $this->assertEquals('string', (string) $type);
    }
}
