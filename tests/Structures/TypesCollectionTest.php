<?php

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;
use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

it('can add a null namespace', function () {
    $structure = TypesCollection::create();

    $structure[] = $fake = FakeTransformedType::fake('Enum')->withoutNamespace();

    assertCount(1, $structure);
    assertEquals([
        'Enum' => $fake,
    ], iterator_to_array($structure));
});

it('can add types in a multi layered namespaces', function () {
    $structure = TypesCollection::create();

    $structure[] = $fakeC = FakeTransformedType::fake('Enum')->withNamespace('a\b\c');
    $structure[] = $fakeB = FakeTransformedType::fake('Enum')->withNamespace('a\b');
    $structure[] = $fakeA = FakeTransformedType::fake('Enum')->withNamespace('a');
    $structure[] = $fake = FakeTransformedType::fake('Enum')->withoutNamespace();

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

    $structure[] = $fakeA = FakeTransformedType::fake('EnumA')->withNamespace('test');
    $structure[] = $fakeB = FakeTransformedType::fake('EnumB')->withNamespace('test');

    assertCount(2, $structure);
    assertEquals([
        'test\EnumA' => $fakeA,
        'test\EnumB' => $fakeB,
    ], iterator_to_array($structure));
});

it('can add a real type', function () {
    $reflection = new ReflectionClass(TypeScriptEnum::class);

    $structure = TypesCollection::create();

    $structure[] = $fake = FakeTransformedType::fake('TypeScriptEnum')->withReflection($reflection);

    assertCount(1, $structure);
    assertEquals([
        TypeScriptEnum::class => $fake,
    ], iterator_to_array($structure));
});

it('cannot have a namespace and type with the same name', function () {
    $collection = TypesCollection::create();

    $collection[] = $fakeA = FakeTransformedType::fake('Enum')->withNamespace('Enum');
    $collection[] = $fakeB = FakeTransformedType::fake('Enum')->withoutNamespace();
})->throws(SymbolAlreadyExists::class);

it('cannot have a namespace and type with the same name reversed', function () {
    $collection = TypesCollection::create();

    $collection[] = $fakeB = FakeTransformedType::fake('Enum')->withoutNamespace();
    $collection[] = $fakeA = FakeTransformedType::fake('Enum')->withNamespace('Enum');
})->throws(SymbolAlreadyExists::class);

it('can get a type', function () {
    $collection = TypesCollection::create();

    $collection[] = $fake = FakeTransformedType::fake('Enum')->withNamespace('a\b\c');

    assertEquals($fake, $collection['a\b\c\Enum']);
});

it('can get a type in the root namespace', function () {
    $collection = TypesCollection::create();

    $collection[] = $fake = FakeTransformedType::fake('Enum')->withoutNamespace();

    assertEquals($fake, $collection['Enum']);
});

it('when searching a non existing type null is returned', function () {
    $collection = TypesCollection::create();

    assertNull($collection['Enum']);
    assertNull($collection['a\b\Enum']);
    assertNull($collection['a\b\Enum']);
});

it('can add inline types without structure checking', function () {
    $collection = TypesCollection::create();

    $collection[] = $fakeA = FakeTransformedType::fake('Enum')->withoutNamespace()->isInline();
    $collection[] = $fakeB = FakeTransformedType::fake('Enum')->withNamespace('Enum');

    assertEquals($fakeA, $collection['Enum']);
    assertEquals($fakeB, $collection['Enum\Enum']);
});
