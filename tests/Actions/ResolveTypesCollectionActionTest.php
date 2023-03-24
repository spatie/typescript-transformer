<?php

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\Exceptions\NoAutoDiscoverTypesPathsDefined;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\BackedEnumWithoutAnnotation;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnumWithCustomTransformer;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnumWithName;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\NativeEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

beforeEach(function () {
    $this->action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(__DIR__ . '/../FakeClasses/Enum')
            ->transformer(MyclabsEnumTransformer::class)
    );
});

it('will construct the type collection correctly', function () {
    $typesCollection = $this->action->execute();

    assertCount(3, $typesCollection);
});

it('will check if auto discover types paths are defined', function () {
    $this->expectException(NoAutoDiscoverTypesPathsDefined::class);

    $action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
    );

    $action->execute();
});

it('parses a typescript enum correctly', function () {
    $type = $this->action->execute()->get(TypeScriptEnum::class);

    assertEquals("type TypeScriptEnum = 'js';", $type->toString());
    assertEmpty($type->typeReferences);
});

it('parses a typescript enum with name correctly', function () {
    $type = $this->action->execute()->get(TypeScriptEnumWithName::class);

    assertEquals("type EnumWithName = 'js';", $type->toString());
    assertEmpty($type->typeReferences);
});

it('parses a typescript enum with custom transformer correctly', function () {
    $type = $this->action->execute()->get(TypeScriptEnumWithCustomTransformer::class);

    assertEquals("fake", $type->toString());
    assertEmpty($type->typeReferences);
});

it('can parse multiple directories', function () {
    $this->action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(
                __DIR__ . '/../FakeClasses/Enum/',
                __DIR__ . '/../FakeClasses/Integration/'
            )
            ->transformer(MyclabsEnumTransformer::class)
            ->transformer(DtoTransformer::class)
    );

    $types = iterator_to_array($this->action->execute());

    assertCount(8, $types);

    assertArrayHasKey(TypeScriptEnum::class, $types);
    assertArrayHasKey(TypeScriptEnumWithCustomTransformer::class, $types);
    assertArrayHasKey(TypeScriptEnumWithName::class, $types);

    assertArrayHasKey(Dto::class, $types);
    assertArrayHasKey(DtoWithChildren::class, $types);
    assertArrayHasKey(Enum::class, $types);
    assertArrayHasKey(OtherDto::class, $types);
    assertArrayHasKey(YetAnotherDto::class, $types);
});

it('can collect certain types automatically', function () {
    $action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(
                __DIR__ . '/../FakeClasses/',
            )
            ->transformer(DtoTransformer::class)
            ->transformer(NativeEnumTransformer::class, auto: false)
    );

    expect($action->execute()->has(BackedEnumWithoutAnnotation::class))->toBeFalse();

    $action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(
                __DIR__ . '/../FakeClasses/',
            )
            ->transformer(DtoTransformer::class)
            ->transformer(NativeEnumTransformer::class, auto: true)
    );

    expect($action->execute()->has(BackedEnumWithoutAnnotation::class))->toBeTrue();
});
