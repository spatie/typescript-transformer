<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\WriteFilesAction;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

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
