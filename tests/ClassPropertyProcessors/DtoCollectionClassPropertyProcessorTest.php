<?php

namespace Spatie\TypescriptTransformer\Tests\ClassPropertyProcessors;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\TestCase;
use Spatie\DataTransferObject\DataTransferObjectCollection;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\DtoCollectionClassPropertyProcessor;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\DtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\NullableDtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\StringDtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Collections\UntypedDtoCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDtoCollection;

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
            $this->typeResolver->resolve(DtoCollection::class)
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
            $this->typeResolver->resolve(NullableDtoCollection::class)
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
            $this->typeResolver->resolve(StringDtoCollection::class)
        );

        $this->assertEquals('string[]', (string) $type);
    }

    /** @test */
    public function it_will_process_a_dto_collection_without_type()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(UntypedDtoCollection::class)
        );

        $this->assertEquals('array', (string) $type);
    }

    /** @test */
    public function it_will_pass_non_dto_collections()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve('string')
        );

        $this->assertEquals('string', (string) $type);
    }
}
