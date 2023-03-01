<?php

use Spatie\TypeScriptTransformer\Tests\Factories\TransformedTypeFactory;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ReplaceTypeReferencesInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

it('can replace missing symbols', function () {
    $action = new ReplaceTypeReferencesInCollectionAction();

    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('enums\Enum')->build());
    $collection->add(TransformedTypeFactory::create('Dto')
        ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
        ->withTypeReferences('enums\Enum', 'non-existing')
        ->build()
    );

    $collection = $action->execute($collection);

    assertEquals('{enum: enums.Enum, non-existing: any}', $collection->get('Dto')->transformed);
});

it('can replace missing symbols without fully qualified names', function () {
    $action = new ReplaceTypeReferencesInCollectionAction();

    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('enums\Enum')->build());
    $collection->add(
        TransformedTypeFactory::create('Dto')
            ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
            ->withTypeReferences('enums\Enum', 'non-existing')
            ->build()
    );

    $collection = $action->execute($collection, false);

    assertEquals('{enum: Enum, non-existing: any}', $collection->get('Dto')->transformed);
});
