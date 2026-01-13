<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\WriteFilesAction;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('can write files in a directory', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    expect(file_get_contents($this->temporaryDirectory->path('fileA.ts')))->toBe('fileA contents');
    expect(file_get_contents($this->temporaryDirectory->path('fileB.ts')))->toBe('fileB contents');
});

it('can write files in a directory with subdirectories', function () {
    $fileA = new WriteableFile('sub/fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('sub/sub2/fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    expect(file_get_contents($this->temporaryDirectory->path('sub/fileA.ts')))->toBe('fileA contents');
    expect(file_get_contents($this->temporaryDirectory->path('sub/sub2/fileB.ts')))->toBe('fileB contents');
});

it('will store a manifest file', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->writer(new ModuleWriter())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    $manifestPath = $this->temporaryDirectory->path('typescript-transformer-manifest.json');

    expect($manifestPath)->toBeFile();
    expect(json_decode(file_get_contents($manifestPath), true))
        ->toBeArray()
        ->toHaveKeys(['fileA.ts', 'fileB.ts']);
});

it('will not write files that have not changed', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->writer(new ModuleWriter())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    $pathA = $this->temporaryDirectory->path('fileA.ts');
    unlink($pathA);

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    expect(file_exists($pathA))->toBeFalse(); // Since we deleted it, it should not be written again
});

it('will delete older files not present anymore in the manifest', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->writer(new ModuleWriter())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    $pathA = $this->temporaryDirectory->path('fileA.ts');
    unlink($pathA);

    (new WriteFilesAction($config))->execute([$fileB]);

    expect(file_exists($pathA))->toBeFalse();
});

it('will update the manifest file', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->writer(new ModuleWriter())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);
    (new WriteFilesAction($config))->execute([$fileA]);

    $manifestPath = $this->temporaryDirectory->path('typescript-transformer-manifest.json');

    expect($manifestPath)->toBeFile();
    expect(json_decode(file_get_contents($manifestPath), true))
        ->toBeArray()
        ->toHaveKeys(['fileA.ts'])
        ->not->toHaveKey('fileB.ts');
});

it('will not use a manifest file when the writer does not support it', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    (new WriteFilesAction($config))->execute([$fileA, $fileB]);

    expect(file_exists($this->temporaryDirectory->path('typescript-transformer-manifest.json')))->toBeFalse();
});
