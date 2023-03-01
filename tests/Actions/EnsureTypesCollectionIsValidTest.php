<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Actions\EnsureTypesCollectionIsValid;
use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedTypeFactory;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

it('cannot have a namespace and type with the same name', function () {
    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('Enum\Enum')->build());
    $collection->add(TransformedTypeFactory::create('Enum')->build());

    ray($collection);

    (new EnsureTypesCollectionIsValid())->execute($collection);
})->throws(SymbolAlreadyExists::class);

it('cannot have a namespace and type with the same name reversed', function () {
    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('Enum')->build());
    $collection->add(TransformedTypeFactory::create('Enum\Enum')->build());

    (new EnsureTypesCollectionIsValid())->execute($collection);
})->throws(SymbolAlreadyExists::class);

it('can add a null namespace', function () {
    $structure = TypesCollection::create();

    $structure->add($fake = TransformedTypeFactory::create('Enum')->build());

    (new EnsureTypesCollectionIsValid())->execute($structure);

    assertCount(1, $structure);
    assertEquals([
        'Enum' => $fake,
    ], iterator_to_array($structure));
});

it('can add types in a multi layered namespaces', function () {
    $structure = TypesCollection::create();

    $structure->add($fakeC = TransformedTypeFactory::create('a\b\c\Enum')->build());
    $structure->add($fakeB = TransformedTypeFactory::create('a\b\Enum')->build());
    $structure->add($fakeA = TransformedTypeFactory::create('a\Enum')->build());
    $structure->add($fake = TransformedTypeFactory::create('Enum')->build());

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

    $structure->add($fakeA = TransformedTypeFactory::create('test\EnumA')->build());
    $structure->add($fakeB = TransformedTypeFactory::create('test\EnumB')->build());

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

    $structure->add($fake = TransformedTypeFactory::create('TypeScriptEnum')->withReflection($reflection)->build());

    (new EnsureTypesCollectionIsValid())->execute($structure);

    assertCount(1, $structure);
    assertEquals([
        TypeScriptEnum::class => $fake,
    ], iterator_to_array($structure));
});

it('can add inline types without structure checking', function () {
    $collection = TypesCollection::create();

    $collection->add($fakeA = TransformedTypeFactory::create('Enum')->isInline()->build());
    $collection->add($fakeB = TransformedTypeFactory::create('Enum\Enum')->isInline()->build());

    (new EnsureTypesCollectionIsValid())->execute($collection);

    assertEquals($fakeA, $collection->get('Enum'));
    assertEquals($fakeB, $collection->get('Enum\Enum'));
});

