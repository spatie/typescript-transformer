<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ReplaceTypeReferencesInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;

it('can replace missing symbols', function () {
    $action = new ReplaceTypeReferencesInCollectionAction();

    $collection = TypesCollection::create();

    $collection->add(TransformedFactory::create('enums\Enum')->build());
    $collection->add(
        TransformedFactory::create('Dto')
        ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
        ->withTypeReferences('enums\Enum', 'non-existing')
        ->build()
    );

    $collection = $action->execute($collection);

    assertEquals('{enum: enums.Enum, non-existing: any}', $collection->get('Dto')->toString());
});

it('can replace missing symbols without fully qualified names', function () {
    $action = new ReplaceTypeReferencesInCollectionAction();

    $collection = TypesCollection::create();

    $collection->add(TransformedFactory::create('enums\Enum')->build());
    $collection->add(
        TransformedFactory::create('Dto')
            ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
            ->withTypeReferences('enums\Enum', 'non-existing')
            ->build()
    );

    $collection = $action->execute($collection, false);

    assertEquals('{enum: Enum, non-existing: any}', $collection->get('Dto')->toString());
});
