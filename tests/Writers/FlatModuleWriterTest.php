<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

beforeEach(function () {
    $this->filename = 'types.ts';

    $this->writer = new FlatModuleWriter($this->filename);
});


it('can write everything in one flat file', function () {
    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias(
            name: 'RootType',
            typeScriptNode: new TypeScriptString(),
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'RootType2',
            typeScriptNode: new TypeScriptString(),
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'Level1Type',
            typeScriptNode: new TypeScriptString(),
            location: ['level1'],
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'Level1Type2',
            typeScriptNode: new TypeScriptString(),
            location: ['level1'],
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'Level2Type',
            typeScriptNode: new TypeScriptString(),
            location: ['level1', 'level2'],
            writer: $this->writer
        )->build(),
    ]);

    [$file] = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($file)
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe($this->filename)
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
        TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeScriptString(),
            reference: $referenceA,
            location: ['nested'],
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'B',
            typeScriptNode: new TypeScriptString(),
            reference: $referenceB,
            location: ['nested', 'subNested'],
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'C',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('a', new TypeScriptReference($referenceA)),
                new TypeScriptProperty('b', new TypeScriptReference($referenceB)),
            ]),
            writer: $this->writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    [$file] = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($file)
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe($this->filename)
        ->contents->toBe(<<<TS
export type A = string;
export type B = string;
export type C = {
a: A
b: B
};

TS);
});

it('can reference types from another flat module writer', function () {
    $writerA = new FlatModuleWriter('types-a.ts');
    $writerB = new FlatModuleWriter('types-b.ts');

    $transformedCollection = new TransformedCollection([
        $referencedType = TransformedFactory::alias(
            name: 'ReferencedType',
            typeScriptNode: new TypeScriptString(),
            writer: $writerA
        )->build(),
        $localType = TransformedFactory::alias(
            name: 'LocalType',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('referenced', new TypeScriptReference($referencedType->reference)),
            ]),
            references: [$referencedType],
            writer: $writerB
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $filesA = $writerA->output(
        [$referencedType],
        $transformedCollection,
    );

    expect($filesA)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($filesA[0])
        ->path->toBe('types-a.ts')
        ->contents->toEqual(
            <<<TS
export type ReferencedType = string;

TS
        );

    $filesB = $writerB->output(
        [$localType],
        $transformedCollection,
    );

    expect($filesB)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($filesB[0])
        ->path->toBe('types-b.ts')
        ->contents->toEqual(
            <<<TS
import { ReferencedType } from './types-a';
export type LocalType = {
referenced: ReferencedType
};

TS
        );
});

it('can reference types from a global namespace writer and module writer', function () {
    $moduleWriter = new ModuleWriter(path: null);
    $globalWriter = new GlobalNamespaceWriter('global-types.ts');
    $flatWriter = new FlatModuleWriter('flat-types.ts');

    $transformedCollection = new TransformedCollection([
        $moduleType = TransformedFactory::alias(
            name: 'ModuleType',
            typeScriptNode: new TypeScriptString(),
            location: ['models'],
            writer: $moduleWriter
        )->build(),
        $globalType = TransformedFactory::alias(
            name: 'GlobalType',
            typeScriptNode: new TypeScriptString(),
            location: ['App', 'Models'],
            writer: $globalWriter
        )->build(),
        $flatType = TransformedFactory::alias(
            name: 'FlatType',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('module', new TypeScriptReference($moduleType->reference)),
                new TypeScriptProperty('global', new TypeScriptReference($globalType->reference)),
            ]),
            references: [$moduleType, $globalType],
            writer: $flatWriter
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $flatFiles = $flatWriter->output(
        [$flatType],
        $transformedCollection,
    );

    expect($flatFiles)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($flatFiles[0])
        ->path->toBe('flat-types.ts')
        ->contents->toEqual(
            <<<TS
import { ModuleType } from './models';
export type FlatType = {
module: ModuleType
global: App.Models.GlobalType
};

TS
        );
});
