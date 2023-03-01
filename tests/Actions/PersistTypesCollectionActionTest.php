<?php

use Spatie\TypeScriptTransformer\FileSplitters\SingleFileSplitter;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedTypeFactory;
use function Spatie\Snapshots\assertMatchesFileSnapshot;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();

    $this->action = new PersistTypesCollectionAction(
        TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(__DIR__ . '/../FakeClasses')
            ->transformers([MyclabsEnumTransformer::class])
            ->outputPath(($this->temporaryDirectory->path()))
    );
});

it('will persist the types', function () {
    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('Enum')->build());
    $collection->add(TransformedTypeFactory::create('test\Enum')->build());
    $collection->add(TransformedTypeFactory::create('test\test\Enum')->build());

    $this->action->execute($collection);

    assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
});

it('can persist multiple types in one namespace', function () {
    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('Enum')->withTransformed('transformed Enum')->build());
    $collection->add(TransformedTypeFactory::create('OtherEnum')->withTransformed('transformed OtherEnum')->build());
    $collection->add(TransformedTypeFactory::create('test\Enum')->withTransformed('transformed test\Enum')->build());
    $collection->add(TransformedTypeFactory::create('test\OtherEnum')->withTransformed('transformed test\OtherEnum')->build());

    $this->action->execute($collection);

    assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
});

it('can re save the file', function () {
    $collection = TypesCollection::create();

    $collection->add(TransformedTypeFactory::create('Enum')->build());

    $this->action->execute($collection);

    $collection->add(TransformedTypeFactory::create('test\Enum')->build());

    $this->action->execute($collection);

    assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
});
