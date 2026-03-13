<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('can handle the integration test with a flat file', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->transformer(new EnumTransformer())
        ->transformer(new AllClassTransformer())
        ->transformDirectories(__DIR__.'/Fakes/Integration')
        ->replaceType(DateTime::class, 'string')
        ->writer(new FlatModuleWriter('flat.d.ts'));

    TypeScriptTransformer::create($config)->execute();

    $content = file_get_contents($this->temporaryDirectory->path('flat.d.ts'));
    $blocks = array_filter(preg_split('/(?=^export type )/m', trim($content)), fn ($b) => trim($b) !== '');
    sort($blocks);

    expect(implode('', $blocks))->toMatchSnapshot();
});

it('can handle the integration test with a namespaced file', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->transformer(new EnumTransformer())
        ->transformer(new AllClassTransformer())
        ->transformDirectories(__DIR__.'/Fakes/Integration')
        ->replaceType(DateTime::class, 'string')
        ->writer(new GlobalNamespaceWriter('flat.d.ts'));

    TypeScriptTransformer::create($config)->execute();

    expect(file_get_contents($this->temporaryDirectory->path('flat.d.ts')))->toMatchSnapshot();
});

it('can handle the integration test with a module structure', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->transformer(new EnumTransformer())
        ->transformer(new AllClassTransformer())
        ->transformDirectories(__DIR__.'/Fakes/Integration')
        ->replaceType(DateTime::class, 'string')
        ->writer(new ModuleWriter('.'));

    TypeScriptTransformer::create($config)->execute();

    expect(file_get_contents($this->temporaryDirectory->path('Spatie/TypeScriptTransformer/Tests/Fakes/Integration/index.ts')))->toMatchSnapshot();
    expect(file_get_contents($this->temporaryDirectory->path('Spatie/TypeScriptTransformer/Tests/Fakes/Integration/Level/index.ts')))->toMatchSnapshot();
});
