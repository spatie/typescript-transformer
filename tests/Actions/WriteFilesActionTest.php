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
    $fileA = new WriteableFile($this->temporaryDirectory->path('fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($this->temporaryDirectory->path('fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->get()))->execute([$fileA, $fileB]);

    expect(file_get_contents($fileA->path))->toBe('fileA contents');
    expect(file_get_contents($fileB->path))->toBe('fileB contents');
});

it('can write files in a directory with subdirectories', function () {
    $fileA = new WriteableFile($this->temporaryDirectory->path('sub/fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($this->temporaryDirectory->path('sub/sub2/fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->get()))->execute([$fileA, $fileB]);

    expect(file_get_contents($fileA->path))->toBe('fileA contents');
    expect(file_get_contents($fileB->path))->toBe('fileB contents');
});

it('will store a manifest file', function () {
    $fileA = new WriteableFile($pathA = $this->temporaryDirectory->path('fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($pathB = $this->temporaryDirectory->path('fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileA, $fileB]);

    $manifestPath = $this->temporaryDirectory->path('typescript-transformer-manifest.json');

    expect($manifestPath)->toBeFile();
    expect(json_decode(file_get_contents($manifestPath), true))
        ->toBeArray()
        ->toHaveKeys([$pathA, $pathB]);
});

it('will not write files that have not changed', function () {
    $fileA = new WriteableFile($pathA = $this->temporaryDirectory->path('fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($pathB = $this->temporaryDirectory->path('fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileA, $fileB]);

    unlink($pathA);

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileA, $fileB]);

    expect(file_exists($pathA))->toBeFalse(); // Since we deleted it, it should not be written again
});

it('will delete older files not present anymore in the manifest', function () {
    $fileA = new WriteableFile($pathA = $this->temporaryDirectory->path('fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($pathB = $this->temporaryDirectory->path('fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileA, $fileB]);

    unlink($pathA);

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileB]);

    expect(file_exists($pathA))->toBeFalse();
});

it('will update the manifest file', function () {
    $fileA = new WriteableFile($pathA = $this->temporaryDirectory->path('fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($pathB = $this->temporaryDirectory->path('fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileA, $fileB]);
    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->writer(new ModuleWriter($this->temporaryDirectory->path()))->get()))->execute([$fileA]);

    $manifestPath = $this->temporaryDirectory->path('typescript-transformer-manifest.json');

    expect($manifestPath)->toBeFile();
    expect(json_decode(file_get_contents($manifestPath), true))
        ->toBeArray()
        ->toHaveKeys([$pathA])
        ->not->toHaveKey($pathB);
});

it('will not use a manifest file when the writer does not support it', function () {
    $fileA = new WriteableFile($pathA = $this->temporaryDirectory->path('fileA.ts'), 'fileA contents');
    $fileB = new WriteableFile($pathB = $this->temporaryDirectory->path('fileB.ts'), 'fileB contents');

    (new WriteFilesAction(TypeScriptTransformerConfigFactory::create()->get()))->execute([$fileA, $fileB]);

    expect(file_exists($this->temporaryDirectory->path('typescript-transformer-manifest.json')))->toBeFalse();
});
