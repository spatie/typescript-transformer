<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ReplaceTypeReferencesInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

it('can replace missing symbols', function () {
    $action = new ReplaceTypeReferencesInCollectionAction();

    $collection = TypesCollection::create();

    $collection->add(FakeTransformedType::fake('Enum')->withNamespace('enums'));
    $collection->add(FakeTransformedType::fake('Dto')
        ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
        ->withTypeReferences([
            'enum' => 'enums\Enum',
            'non-existing' => 'non-existing',
        ])
    );

    $collection = $action->execute($collection);

    assertEquals('{enum: enums.Enum, non-existing: any}', $collection->get('Dto')->transformed);
});

it('can replace missing symbols without fully qualified names', function () {
    $action = new ReplaceTypeReferencesInCollectionAction();

    $collection = TypesCollection::create();

    $collection->add(FakeTransformedType::fake('Enum')->withNamespace('enums'));
    $collection->add(
        FakeTransformedType::fake('Dto')
            ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
            ->withTypeReferences([
                'enum' => 'enums\Enum',
                'non-existing' => 'non-existing',
            ])
    );

    $collection = $action->execute($collection, false);

    assertEquals('{enum: Enum, non-existing: any}', $collection->get('Dto')->transformed);
});
