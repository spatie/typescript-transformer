<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

beforeEach(function () {
    $this->temporaryDirectory = (new TemporaryDirectory())->create();

    $this->outputFile = $this->temporaryDirectory->path('types.d.ts');
})->skipOnWindows();

it('can format an generated file with prettier', function () {
    $contentA = "export type Enum='yes'|'no';export type OtherDto={name:string}";
    $contentB = '{int: number;overwritable: number | boolean;object: {an_int:number;a_bool:boolean;}pure_typescript: never;pure_typescript_object: {an_any:any;a_never:never;}regular_type: number;}';

    file_put_contents($this->temporaryDirectory->path('testA.ts'), $contentA);
    file_put_contents($this->temporaryDirectory->path('testB.ts'), $contentB);

    $writeableFiles = [
        new WriteableFile('testA.ts', $contentA, changed: true),
        new WriteableFile('testB.ts', $contentB, changed: true),
    ];

    $action = new FormatFilesAction(
        TypeScriptTransformerConfigFactory::create()
            ->outputDirectory($this->temporaryDirectory->path())
            ->formatter(PrettierFormatter::class)
            ->get()
    );

    $action->execute($writeableFiles);

    assertMatchesSnapshot(file_get_contents($this->temporaryDirectory->path('testA.ts')));
    assertMatchesSnapshot(file_get_contents($this->temporaryDirectory->path('testB.ts')));
});

it('can disable formatting', function () {
    $contentA = "export type Enum='yes'|'no';export type OtherDto={name:string}";
    $contentB = '{int: number;overwritable: number | boolean;object: {an_int:number;a_bool:boolean;}pure_typescript: never;pure_typescript_object: {an_any:any;a_never:never;}regular_type: number;}';

    file_put_contents($this->temporaryDirectory->path('testA.ts'), $contentA);
    file_put_contents($this->temporaryDirectory->path('testB.ts'), $contentB);

    $writeableFiles = [
        new WriteableFile('testA.ts', $contentA, changed: true),
        new WriteableFile('testB.ts', $contentB, changed: true),
    ];

    $action = new FormatFilesAction(
        TypeScriptTransformerConfigFactory::create()
            ->outputDirectory($this->temporaryDirectory->path())
            ->get()
    );

    $action->execute($writeableFiles);

    assertMatchesSnapshot(file_get_contents($this->temporaryDirectory->path('testA.ts')));
    assertMatchesSnapshot(file_get_contents($this->temporaryDirectory->path('testB.ts')));
});

it('only formats changed files', function () {
    $contentA = "export type Enum='yes'|'no';";
    $contentB = '{int: number;}';

    file_put_contents($this->temporaryDirectory->path('testA.ts'), $contentA);
    file_put_contents($this->temporaryDirectory->path('testB.ts'), $contentB);

    $writeableFiles = [
        new WriteableFile('testA.ts', $contentA, changed: true),
        new WriteableFile('testB.ts', $contentB, changed: false),
    ];

    $action = new FormatFilesAction(
        TypeScriptTransformerConfigFactory::create()
            ->outputDirectory($this->temporaryDirectory->path())
            ->formatter(PrettierFormatter::class)
            ->get()
    );

    $action->execute($writeableFiles);

    expect(file_get_contents($this->temporaryDirectory->path('testA.ts')))->toBe("export type Enum = \"yes\" | \"no\";\n");
    expect(file_get_contents($this->temporaryDirectory->path('testB.ts')))->toBe($contentB);
});
