<?php

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;
use Spatie\TypeScriptTransformer\Actions\ResolveTypesCollectionAction;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Exceptions\NoAutoDiscoverTypesPathsDefined;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnumWithCustomTransformer;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnumWithName;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDtoCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTypeScriptCollector;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

beforeEach(function () {
    $this->action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(__DIR__ . '/../FakeClasses/Enum')
            ->transformers([MyclabsEnumTransformer::class])
            ->collectors([DefaultCollector::class])
            ->outputFile('types.d.ts')
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
    $type = $this->action->execute()[TypeScriptEnum::class];

    assertEquals(new ReflectionClass(new TypeScriptEnum('js')), $type->reflection);
    assertEquals('TypeScriptEnum', $type->name);
    assertEquals("'js'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
});

it('parses a typescript enum with name correctly', function () {
    $type = $this->action->execute()[TypeScriptEnumWithName::class];

    assertEquals(new ReflectionClass(new TypeScriptEnumWithName('js')), $type->reflection);
    assertEquals('EnumWithName', $type->name);
    assertEquals("'js'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
});

it('parses a typescript enum with custom transformer correctly', function () {
    $type = $this->action->execute()[TypeScriptEnumWithCustomTransformer::class];

    assertEquals(new ReflectionClass(new TypeScriptEnumWithCustomTransformer('js')), $type->reflection);
    assertEquals('TypeScriptEnumWithCustomTransformer', $type->name);
    assertEquals("fake", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
});

it('can parse multiple directories', function () {
    $this->action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
        ->autoDiscoverTypes(
            __DIR__ . '/../FakeClasses/Enum/',
            __DIR__ . '/../FakeClasses/Integration/'
        )
        ->transformers([MyclabsEnumTransformer::class, DtoTransformer::class])
        ->collectors([DefaultCollector::class])
        ->outputFile('types.d.ts')
    );

    $types = $this->action->execute();

    assertCount(9, $types);

    assertArrayHasKey(TypeScriptEnum::class, $types);
    assertArrayHasKey(TypeScriptEnumWithCustomTransformer::class, $types);
    assertArrayHasKey(TypeScriptEnumWithName::class, $types);

    assertArrayHasKey(Dto::class, $types);
    assertArrayHasKey(DtoWithChildren::class, $types);
    assertArrayHasKey(Enum::class, $types);
    assertArrayHasKey(OtherDto::class, $types);
    assertArrayHasKey(OtherDtoCollection::class, $types);
    assertArrayHasKey(YetAnotherDto::class, $types);
});

it('can add an collector for types', function () {
    $this->action = new ResolveTypesCollectionAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
        ->autoDiscoverTypes(__DIR__ . '/../FakeClasses/Enum')
        ->collectors([FakeTypeScriptCollector::class])
        ->outputFile('types.d.ts')
    );

    $types = $this->action->execute();

    assertCount(4, $types);
    assertArrayHasKey(RegularEnum::class, $types);
    assertArrayHasKey(TypeScriptEnum::class, $types);
    assertArrayHasKey(TypeScriptEnumWithCustomTransformer::class, $types);
    assertArrayHasKey(TypeScriptEnumWithName::class, $types);
});
