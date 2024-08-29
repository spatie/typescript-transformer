<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Writers\FlatWriter;

beforeEach(function () {
    $this->path = '/some/path';

    $this->writer = new FlatWriter($this->path);
});


it('can write everything in one flat file', function () {
    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('RootType', new TypeScriptString())->build(),
        TransformedFactory::alias('RootType2', new TypeScriptString())->build(),
        TransformedFactory::alias('Level1Type', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level1Type2', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level2Type', new TypeScriptString(), location: ['level1', 'level2'])->build(),
    ]);

    [$file] = $this->writer->output(
        $transformedCollection,
        new ReferenceMap(),
    );

    expect($file)
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe($this->path)
        ->contents->toBe(<<<TS
export type RootType = string;
export type RootType2 = string;
export type Level1Type = string;
export type Level1Type2 = string;
export type Level2Type = string;

TS);
});

it('can reference to other types in a flat file', function () {
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

    [$file] = $this->writer->output(
        $transformedCollection,
        (new ConnectReferencesAction())->execute($transformedCollection),
    );

    expect($file)
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe($this->path)
        ->contents->toBe(<<<TS
export type A = string;
export type B = string;
export type C = {
a: A
b: B
};

TS);
});
