<?php

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\String_;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

beforeEach(function () {
    $this->typeResolver = new TypeResolver();

    $this->processor = new ReplaceDefaultsTypeProcessor([
        DateTime::class => new String_(),
        Dto::class => new TypeScriptType('array'),
    ]);
});

it('can replace types', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve(Dto::class),
        FakeReflectionProperty::create(),
        new TypeReferencesCollection()
    );

    assertEquals(new TypeScriptType('array'), $type);
});

it('can replace types as nullable', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve('?' . DateTime::class),
        FakeReflectionProperty::create(),
        new TypeReferencesCollection()
    );

    assertEquals(new Nullable(new String_()), $type);
});

it('can replace types in arrays', function () {
    $type = $this->processor->process(
        $this->typeResolver->resolve(DateTime::class . '[]'),
        FakeReflectionProperty::create(),
        new TypeReferencesCollection()
    );

    assertEquals(new Array_(new String_()), $type);
});
