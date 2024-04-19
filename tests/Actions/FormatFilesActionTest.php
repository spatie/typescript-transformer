<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();

    $this->outputFile = $this->temporaryDirectory->path('types.d.ts');
});

it('can format an generated file with prettier', function () {
    $writeableFileA = new WriteableFile(
        $this->temporaryDirectory->path('testA.ts'),
        "export type Enum='yes'|'no';export type OtherDto={name:string}"
    );

    $writeableFileB = new WriteableFile(
        $this->temporaryDirectory->path('testA.ts'),
        '{int: number;overwritable: number | boolean;object: {an_int:number;a_bool:boolean;}pure_typescript: never;pure_typescript_object: {an_any:any;a_never:never;}regular_type: number;}'
    );

    file_put_contents($writeableFileA->path, $writeableFileA->contents);
    file_put_contents($writeableFileB->path, $writeableFileB->contents);

    $action = new FormatFilesAction(
        TypeScriptTransformerConfigFactory::create()
            ->formatter(PrettierFormatter::class)
            ->get()
    );

    $action->execute([
        $writeableFileA,
        $writeableFileB,
    ]);

    assertMatchesSnapshot(file_get_contents($writeableFileA->path));
    assertMatchesSnapshot(file_get_contents($writeableFileB->path));
});

it('can disable formatting', function () {
    $writeableFileA = new WriteableFile(
        $this->temporaryDirectory->path('testA.ts'),
        "export type Enum='yes'|'no';export type OtherDto={name:string}"
    );

    $writeableFileB = new WriteableFile(
        $this->temporaryDirectory->path('testA.ts'),
        '{int: number;overwritable: number | boolean;object: {an_int:number;a_bool:boolean;}pure_typescript: never;pure_typescript_object: {an_any:any;a_never:never;}regular_type: number;}'
    );

    file_put_contents($writeableFileA->path, $writeableFileA->contents);
    file_put_contents($writeableFileB->path, $writeableFileB->contents);

    $action = new FormatFilesAction(
        TypeScriptTransformerConfigFactory::create()->get()
    );

    $action->execute([
        $writeableFileA,
        $writeableFileB,
    ]);

    assertMatchesSnapshot(file_get_contents($writeableFileA->path));
    assertMatchesSnapshot(file_get_contents($writeableFileB->path));
});
