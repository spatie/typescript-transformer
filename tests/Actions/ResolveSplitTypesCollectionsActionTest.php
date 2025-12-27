<?php

use Spatie\TypeScriptTransformer\Actions\ResolveSplitTypesCollectionsAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertContains;
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
    $this->action = new ResolveSplitTypesCollectionsAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(__DIR__ . '/../FakeClasses/Enum')
            ->transformers([MyclabsEnumTransformer::class])
            ->collectors([DefaultCollector::class])
            ->splitModulesBaseDir('data')
    );
});

it('will construct the type collections correctly', function () {
    $typesCollections = $this->action->execute();

    assertCount(1, $typesCollections);
});

it('will check if auto discover types paths are defined', function () {
    $this->expectException(NoAutoDiscoverTypesPathsDefined::class);

    $action = new ResolveSplitTypesCollectionsAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
    );

    $action->execute();
});

it('parses a typescript enum correctly', function () {
    $collections = $this->action->execute();
    $type = $collections[array_key_first($collections)][TypeScriptEnum::class];

    assertEquals(new ReflectionClass(new TypeScriptEnum('js')), $type->reflection);
    assertEquals('TypeScriptEnum', $type->name);
    assertEquals("'js'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
});

it('parses a typescript enum with name correctly', function () {
    $collections = $this->action->execute();
    $type = $collections[array_key_first($collections)][TypeScriptEnumWithName::class];

    assertCount(1, $collections);
    assertEquals(new ReflectionClass(new TypeScriptEnumWithName('js')), $type->reflection);
    assertEquals('EnumWithName', $type->name);
    assertEquals("'js'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
});

it('parses a typescript enum with custom transformer correctly', function () {
    $collections = $this->action->execute();
    $type = $collections[array_key_first($collections)][TypeScriptEnumWithCustomTransformer::class];

    assertEquals(new ReflectionClass(new TypeScriptEnumWithCustomTransformer('js')), $type->reflection);
    assertEquals('TypeScriptEnumWithCustomTransformer', $type->name);
    assertEquals("fake", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
});

it('can parse multiple directories', function () {
    $this->action = new ResolveSplitTypesCollectionsAction(
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

    $collections = $this->action->execute();

    $types = new TypesCollection();
    foreach ($collections as $collection) {
        foreach ($collection as $type) {
            $types[] = $type;
        }
    }

    assertCount(3, $collections);
    assertArrayHasKey("Spatie/TypeScriptTransformer/Tests/FakeClasses/Enum", $collections);
    assertArrayHasKey("Spatie/TypeScriptTransformer/Tests/FakeClasses/Integration", $collections);
    assertArrayHasKey("Spatie/TypeScriptTransformer/Tests/FakeClasses/Integration/LevelUp", $collections);
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

it('can add a collector for types', function () {
    $this->action = new ResolveSplitTypesCollectionsAction(
        new Finder(),
        TypeScriptTransformerConfig::create()
        ->autoDiscoverTypes(__DIR__ . '/../FakeClasses/Enum')
        ->collectors([FakeTypeScriptCollector::class])
        ->outputFile('types.d.ts')
    );

    $collections = $this->action->execute();
    $types = $collections[array_key_first($collections)];

    assertCount(1, $collections);
    assertCount(4, $types);
    assertArrayHasKey(RegularEnum::class, $types);
    assertArrayHasKey(TypeScriptEnum::class, $types);
    assertArrayHasKey(TypeScriptEnumWithCustomTransformer::class, $types);
    assertArrayHasKey(TypeScriptEnumWithName::class, $types);
});
