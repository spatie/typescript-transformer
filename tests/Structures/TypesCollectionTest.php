<?php

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;
use Spatie\TypeScriptTransformer\Exceptions\SymbolAlreadyExists;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

it('can get a type', function () {
    $collection = TypesCollection::create();

    $collection->add($fake = FakeTransformedType::fake('Enum')->withNamespace('a\b\c'));

    assertEquals($fake, $collection->get('a\b\c\Enum'));
});

it('can get a type in the root namespace', function () {
    $collection = TypesCollection::create();

    $collection->add($fake = FakeTransformedType::fake('Enum')->withoutNamespace());

    assertEquals($fake, $collection->get('Enum'));
});

it('when searching a non existing type null is returned', function () {
    $collection = TypesCollection::create();

    assertNull($collection->get('Enum'));
    assertNull($collection->get('a\b\Enum'));
    assertNull($collection->get('a\b\Enum'));
});

it('can count types', function () {
    $collection = TypesCollection::create();

    $collection->add(FakeTransformedType::fake('EnumA')->withoutNamespace());
    $collection->add(FakeTransformedType::fake('EnumB')->withoutNamespace());

    expect($collection)->toHaveCount(2);
});

it('can iterate over types', function () {
    $collection = TypesCollection::create();

    $collection->add($fakeA = FakeTransformedType::fake('EnumA')->withoutNamespace());
    $collection->add($fakeB = FakeTransformedType::fake('EnumB')->withoutNamespace());

    $types = [];

    foreach ($collection as $type){
        $types[] = $type;
    }

    expect($types)
        ->toHaveCount(2)
        ->toContain($fakeA)
        ->toContain($fakeB);
});
