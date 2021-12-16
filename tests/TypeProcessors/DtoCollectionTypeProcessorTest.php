<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeProcessors;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\DtoCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\NullableDtoCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\StringDtoCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\UntypedDtoCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;

class DtoCollectionTypeProcessorTest extends TestCase
{
    private DtoCollectionTypeProcessor $processor;

    private TypeResolver $typeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeResolver = new TypeResolver();

        $this->processor = new DtoCollectionTypeProcessor();
    }

    /** @test */
    public function it_will_process_a_dto_collection()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(DtoCollection::class),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals(
            '\Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto[]',
            (string) $type
        );
    }

    /** @test */
    public function it_will_process_a_nullable_dto_collection()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(NullableDtoCollection::class),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals(
            '?\Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto[]',
            (string) $type
        );
    }

    /** @test */
    public function it_will_process_a_dto_collection_with_built_in_type()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(StringDtoCollection::class),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals('string[]', (string) $type);
    }

    /** @test */
    public function it_will_process_a_dto_collection_without_type()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(UntypedDtoCollection::class),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals(new Array_(new Mixed_()), $type);
    }

    /** @test */
    public function it_will_pass_non_dto_collections()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve('string'),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals('string', (string) $type);
    }
}
