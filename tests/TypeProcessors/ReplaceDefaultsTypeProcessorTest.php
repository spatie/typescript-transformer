<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeProcessors;

use DateTime;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

class ReplaceDefaultsTypeProcessorTest extends TestCase
{
    private ReplaceDefaultsTypeProcessor $processor;

    private TypeResolver $typeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeResolver = new TypeResolver();

        $this->processor = new ReplaceDefaultsTypeProcessor([
            DateTime::class => new String_(),
            Dto::class => new TypeScriptType('array'),
        ]);
    }

    /** @test */
    public function it_can_replace_types()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(Dto::class),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals(new TypeScriptType('array'), $type);
    }

    /** @test */
    public function it_can_replace_types_as_nullable()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve('?' . DateTime::class),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals(new Nullable(new String_()), $type);
    }

    /** @test */
    public function it_can_replace_types_in_arrays()
    {
        $type = $this->processor->process(
            $this->typeResolver->resolve(DateTime::class . '[]'),
            FakeReflectionProperty::create(),
            new MissingSymbolsCollection()
        );

        $this->assertEquals(new Array_(new String_()), $type);
    }
}
