<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Actions\EnsureTypesCollectionIsValid;
use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

it('cannot have a namespace and type with the same name', function () {
    $collection = TypesCollection::create();

    $collection->add(FakeTransformedType::fake('Enum')->withNamespace('Enum'));
    $collection->add(FakeTransformedType::fake('Enum')->withoutNamespace());

    (new EnsureTypesCollectionIsValid())->execute($collection);
})->throws(SymbolAlreadyExists::class);

it('cannot have a namespace and type with the same name reversed', function () {
    $collection = TypesCollection::create();

    $collection->add(FakeTransformedType::fake('Enum')->withoutNamespace());
    $collection->add(FakeTransformedType::fake('Enum')->withNamespace('Enum'));

    (new EnsureTypesCollectionIsValid())->execute($collection);
})->throws(SymbolAlreadyExists::class);

it('can add a null namespace', function () {
    $structure = TypesCollection::create();

    $structure->add($fake = FakeTransformedType::fake('Enum')->withoutNamespace());

    (new EnsureTypesCollectionIsValid())->execute($structure);

    assertCount(1, $structure);
    assertEquals([
        'Enum' => $fake,
    ], iterator_to_array($structure));
});

it('can add types in a multi layered namespaces', function () {
    $structure = TypesCollection::create();

    $structure->add($fakeC = FakeTransformedType::fake('Enum')->withNamespace('a\b\c'));
    $structure->add($fakeB = FakeTransformedType::fake('Enum')->withNamespace('a\b'));
    $structure->add($fakeA = FakeTransformedType::fake('Enum')->withNamespace('a'));
    $structure->add($fake = FakeTransformedType::fake('Enum')->withoutNamespace());

    (new EnsureTypesCollectionIsValid())->execute($structure);

    assertCount(4, $structure);
    assertEquals([
        'Enum' => $fake,
        'a\Enum' => $fakeA,
        'a\b\Enum' => $fakeB,
        'a\b\c\Enum' => $fakeC,
    ], iterator_to_array($structure));
});

it('can add multiple types to one namespace', function () {
    $structure = TypesCollection::create();

    $structure->add($fakeA = FakeTransformedType::fake('EnumA')->withNamespace('test'));
    $structure->add($fakeB = FakeTransformedType::fake('EnumB')->withNamespace('test'));

    (new EnsureTypesCollectionIsValid())->execute($structure);

    assertCount(2, $structure);
    assertEquals([
        'test\EnumA' => $fakeA,
        'test\EnumB' => $fakeB,
    ], iterator_to_array($structure));
});

it('can add a real type', function () {
    $reflection = new ReflectionClass(TypeScriptEnum::class);

    $structure = TypesCollection::create();

    $structure->add($fake = FakeTransformedType::fake('TypeScriptEnum')->withReflection($reflection));

    (new EnsureTypesCollectionIsValid())->execute($structure);

    assertCount(1, $structure);
    assertEquals([
        TypeScriptEnum::class => $fake,
    ], iterator_to_array($structure));
});

it('can add inline types without structure checking', function () {
    $collection = TypesCollection::create();

    $collection->add($fakeA = FakeTransformedType::fake('Enum')->withoutNamespace()->isInline());
    $collection->add($fakeB = FakeTransformedType::fake('Enum')->withNamespace('Enum'));

    (new EnsureTypesCollectionIsValid())->execute($collection);

    assertEquals($fakeA, $collection->get('Enum'));
    assertEquals($fakeB, $collection->get('Enum\Enum'));
});

