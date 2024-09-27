<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Writers\NamespaceWriter;

it('can write a namespaced file', function () {
    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('RootType', new TypeScriptString())->build(),
        TransformedFactory::alias('RootType2', new TypeScriptString())->build(),
        TransformedFactory::alias('Level1Type', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level1Type2', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level2Type', new TypeScriptString(), location: ['level1', 'level2'])->build(),
    ]);

    $filename = 'types.ts';

    $files = (new NamespaceWriter($filename))->output(
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    $file = $files[0];

    expect($file)
        ->path->toBe($filename)
        ->contents->toEqual(
            <<<TS
    export type RootType = string;
    export type RootType2 = string;
    declare namespace level1{
    export type Level1Type = string;
    export type Level1Type2 = string;
    }
    declare namespace level1.level2{
    export type Level2Type = string;
    }

    TS
        );
});

it('will reference correctly between namespaces', function () {
    $referenceA = new CustomReference('test', 'A');
    $referenceB = new CustomReference('test', 'B');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('A', new TypeScriptString(), reference: $referenceA, location: ['nested'])->build(),
        TransformedFactory::alias('B', new TypeScriptString(), reference: $referenceB, location: ['nested', 'subNested'])->build(),
        TransformedFactory::alias('C', new TypeScriptObject([
            new TypeScriptProperty('a', new TypeReference($referenceA)),
            new TypeScriptProperty('b', new TypeReference($referenceB)),
        ]))->build(),
    ]);

    $filename = 'types.ts';

    (new ConnectReferencesAction(TypeScriptTransformerLog::createNullLog()))->execute($transformedCollection);

    $files = (new NamespaceWriter($filename))->output(
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    $file = $files[0];

    expect($file)
        ->path->toBe($filename)
        ->contents->toEqual(<<<TS
export type C = {
a: nested.A
b: nested.subNested.B
};
declare namespace nested{
export type A = string;
}
declare namespace nested.subNested{
export type B = string;
}

TS);

});
