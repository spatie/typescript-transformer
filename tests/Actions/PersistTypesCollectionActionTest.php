<?php

use function Spatie\Snapshots\assertMatchesFileSnapshot;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();

    $config = TypeScriptTransformerConfig::create()
        ->autoDiscoverTypes(__DIR__ . '/../FakeClasses')
        ->transformers([MyclabsEnumTransformer::class])
        ->outputFile($this->temporaryDirectory->path('types.d.ts'));
    $this->action = new PersistTypesCollectionAction(
        $config,
        $config->getOutputFile()
    );
});

it('will persist the types', function () {
    $collection = TypesCollection::create();

    $collection[] = FakeTransformedType::fake('Enum')->withoutNamespace();
    $collection[] = FakeTransformedType::fake('Enum')->withNamespace('test');
    $collection[] = FakeTransformedType::fake('Enum')->withNamespace('test\test');

    $this->action->execute($collection, $collection);

    assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
});

it('can persist multiple types in one namespace', function () {
    $collection = TypesCollection::create();

    $collection[] = FakeTransformedType::fake('Enum')->withTransformed('transformed Enum')->withoutNamespace();
    $collection[] = FakeTransformedType::fake('OtherEnum')->withTransformed('transformed OtherEnum')->withoutNamespace();
    $collection[] = FakeTransformedType::fake('Enum')->withTransformed('transformed test\Enum')->withNamespace('test');
    $collection[] = FakeTransformedType::fake('OtherEnum')->withTransformed('transformed test\OtherEnum')->withNamespace('test');

    $this->action->execute($collection, $collection);

    assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
});

it('can re save the file', function () {
    $collection = TypesCollection::create();

    $collection[] = FakeTransformedType::fake('Enum')->withoutNamespace();

    $this->action->execute($collection);

    $collection[] = FakeTransformedType::fake('Enum')->withNamespace('test');

    $this->action->execute($collection);

    assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
});
