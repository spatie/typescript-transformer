<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

it('can replace missing symbols', function () {
    $action = new ReplaceSymbolsInCollectionAction();

    $collection = TypesCollection::create();

    $collection[] = FakeTransformedType::fake('Enum')->withNamespace('enums');
    $collection[] = FakeTransformedType::fake('Dto')
        ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
        ->withMissingSymbols([
        'enum' => 'enums\Enum',
        'non-existing' => 'non-existing',
        ]);

    $collection = $action->execute($collection);

    assertEquals('{enum: enums.Enum, non-existing: {[key: string]: unknown}}', $collection['Dto']->transformed);
});

it('can replace missing symbols without fully qualified names', function () {
    $action = new ReplaceSymbolsInCollectionAction();

    $collection = TypesCollection::create();

    $collection[] = FakeTransformedType::fake('Enum')->withNamespace('enums');
    $collection[] = FakeTransformedType::fake('Dto')
        ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
        ->withMissingSymbols([
        'enum' => 'enums\Enum',
        'non-existing' => 'non-existing',
        ]);

    $collection = $action->execute($collection, false);

    assertEquals('{enum: Enum, non-existing: {[key: string]: unknown}}', $collection['Dto']->transformed);
});
