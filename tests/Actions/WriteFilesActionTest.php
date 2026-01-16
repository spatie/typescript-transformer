<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\WriteFilesAction;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('can write files in a directory', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    expect(file_get_contents($this->temporaryDirectory->path('fileA.ts')))->toBe('fileA contents');
    expect(file_get_contents($this->temporaryDirectory->path('fileB.ts')))->toBe('fileB contents');
});

it('can write files in a directory with subdirectories', function () {
    $fileA = new WriteableFile('sub/fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('sub/sub2/fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    expect(file_get_contents($this->temporaryDirectory->path('sub/fileA.ts')))->toBe('fileA contents');
    expect(file_get_contents($this->temporaryDirectory->path('sub/sub2/fileB.ts')))->toBe('fileB contents');
});

it('will store a manifest file', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

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
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    $pathA = $this->temporaryDirectory->path('fileA.ts');
    unlink($pathA);

    $files2 = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files2);

    expect(file_exists($pathA))->toBeFalse();
});

it('will delete older files not present anymore in the manifest', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    $pathA = $this->temporaryDirectory->path('fileA.ts');
    expect(file_exists($pathA))->toBeTrue();

    $files2 = [$fileB];
    (new WriteFilesAction($config))->execute($files2);

    expect(file_exists($pathA))->toBeFalse();
});

it('will update the manifest file', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    $files2 = [$fileA];
    (new WriteFilesAction($config))->execute($files2);

    $manifestPath = $this->temporaryDirectory->path('typescript-transformer-manifest.json');

    expect($manifestPath)->toBeFile();
    expect(json_decode(file_get_contents($manifestPath), true))
        ->toBeArray()
        ->toHaveKeys(['fileA.ts'])
        ->not->toHaveKey('fileB.ts');
});

it('will always create a manifest file', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    expect(file_exists($this->temporaryDirectory->path('typescript-transformer-manifest.json')))->toBeTrue();
});

it('marks only changed files in the array', function () {
    $fileA = new WriteableFile('fileA.ts', 'fileA contents');
    $fileB = new WriteableFile('fileB.ts', 'fileB contents');

    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
        ->get();

    $files = [$fileA, $fileB];
    (new WriteFilesAction($config))->execute($files);

    expect($files[0]->changed)->toBeTrue();
    expect($files[1]->changed)->toBeTrue();

    $fileC = new WriteableFile('fileC.ts', 'fileC contents');
    $files = [$fileA, $fileB, $fileC];
    (new WriteFilesAction($config))->execute($files);

    expect($files[0]->changed)->toBeFalse();
    expect($files[1]->changed)->toBeFalse();
    expect($files[2]->changed)->toBeTrue();

    $fileAUpdated = new WriteableFile('fileA.ts', 'fileA updated contents');
    $files = [$fileAUpdated, $fileB, $fileC];
    (new WriteFilesAction($config))->execute($files);

    expect($files[0]->changed)->toBeTrue();
    expect($files[1]->changed)->toBeFalse();
    expect($files[2]->changed)->toBeFalse();
});
