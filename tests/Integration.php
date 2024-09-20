<?php

use function Spatie\Snapshots\assertMatchesFileSnapshot;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatWriter;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;
use Spatie\TypeScriptTransformer\Writers\NamespaceWriter;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('can handle the integration test with a flat file', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformer(new EnumTransformer())
        ->transformer(new AllClassTransformer())
        ->watchDirectories(__DIR__ . '/Fakes/Integration')
        ->replaceType(DateTime::class, 'string')
        ->writer(new FlatWriter($this->temporaryDirectory->path('flat.d.ts')));

    TypeScriptTransformer::create($config)->execute();

    assertMatchesFileSnapshot($this->temporaryDirectory->path('flat.d.ts'));
});

it('can handle the integration test with a namespaced file', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformer(new EnumTransformer())
        ->transformer(new AllClassTransformer())
        ->watchDirectories(__DIR__ . '/Fakes/Integration')
        ->replaceType(DateTime::class, 'string')
        ->writer(new NamespaceWriter($this->temporaryDirectory->path('flat.d.ts')));

    TypeScriptTransformer::create($config)->execute();

    assertMatchesFileSnapshot($this->temporaryDirectory->path('flat.d.ts'));
});

it('can handle the integration test with a module structure', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformer(new EnumTransformer())
        ->transformer(new AllClassTransformer())
        ->watchDirectories(__DIR__ . '/Fakes/Integration')
        ->replaceType(DateTime::class, 'string')
        ->writer(new ModuleWriter($this->temporaryDirectory->path()));

    TypeScriptTransformer::create($config)->execute();

    assertMatchesFileSnapshot($this->temporaryDirectory->path('Spatie/TypeScriptTransformer/Tests/Fakes/Integration/index.ts'));
    assertMatchesFileSnapshot($this->temporaryDirectory->path('Spatie/TypeScriptTransformer/Tests/Fakes/Integration/Level/index.ts'));
});
