<?php

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\DtoCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\NullableDtoCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\StringDtoCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Collections\UntypedDtoCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

beforeEach(function () {
    $this->typeResolver = new TypeResolver();

    $this->processor = new DtoCollectionTypeProcessor();
});

it('will process a dto collection', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve(DtoCollection::class),
        FakeReflectionProperty::create(),
        new MissingSymbolsCollection()
    );

    assertEquals(
        '\Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto[]',
        (string) $type
    );
});

it('will process a nullable dto collection', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve(NullableDtoCollection::class),
        FakeReflectionProperty::create(),
        new MissingSymbolsCollection()
    );

    assertEquals(
        '?\Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto[]',
        (string) $type
    );
});

it('will process a dto collection with built in type', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve(StringDtoCollection::class),
        FakeReflectionProperty::create(),
        new MissingSymbolsCollection()
    );

    assertEquals('string[]', (string) $type);
});

it('will process a dto collection without type', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve(UntypedDtoCollection::class),
        FakeReflectionProperty::create(),
        new MissingSymbolsCollection()
    );

    assertEquals(new Array_(new TypeScriptType('unknown')), $type);
});

it('will pass non dto collections', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve('string'),
        FakeReflectionProperty::create(),
        new MissingSymbolsCollection()
    );

    assertEquals('string', (string) $type);
});
