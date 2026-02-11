<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

it('can write a namespaced file', function () {
    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('RootType', new TypeScriptString())->build(),
        TransformedFactory::alias('RootType2', new TypeScriptString())->build(),
        TransformedFactory::alias('Level1Type', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level1Type2', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level2Type', new TypeScriptString(), location: ['level1', 'level2'])->build(),
    ]);

    $filename = 'types.ts';

    $files = (new GlobalNamespaceWriter($filename))->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    $file = $files[0];

    expect($file)
        ->path->toBe('types.d.ts')
        ->contents->toEqual(
            <<<TS
    export type RootType = string;
    export type RootType2 = string;
    declare namespace level1 {
    export type Level1Type = string;
    export type Level1Type2 = string;
    namespace level2 {
    export type Level2Type = string;
    }
    }

    TS
        );
});

it('will reference correctly between namespaces', function () {
    $referenceA = new CustomReference('test', 'A');
    $referenceB = new CustomReference('test', 'B');

    $filename = 'types.ts';

    $writer = new GlobalNamespaceWriter($filename);

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeScriptString(),
            reference: $referenceA,
            location: ['nested'],
            writer: $writer
        )->build(),
        TransformedFactory::alias(
            name: 'B',
            typeScriptNode: new TypeScriptString(),
            reference: $referenceB,
            location: ['nested', 'subNested'],
            writer: $writer
        )->build(),
        TransformedFactory::alias(
            name: 'C',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('a', new TypeReference($referenceA)),
                new TypeScriptProperty('b', new TypeReference($referenceB)),
            ]),
            writer: $writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $files = ($writer)->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    $file = $files[0];

    expect($file)
        ->path->toBe('types.d.ts')
        ->contents->toEqual(
            <<<TS
export type C = {
a: nested.A
b: nested.subNested.B
};
declare namespace nested {
export type A = string;
namespace subNested {
export type B = string;
}
}

TS
        );

});

it('can reference types from another global namespace writer', function () {
    $writerA = new GlobalNamespaceWriter('types-a.ts');
    $writerB = new GlobalNamespaceWriter('types-b.ts');

    $transformedCollection = new TransformedCollection([
        $referencedType = TransformedFactory::alias(
            name: 'ReferencedType',
            typeScriptNode: new TypeScriptString(),
            location: ['App', 'Models'],
            writer: $writerA
        )->build(),
        $localType = TransformedFactory::alias(
            name: 'LocalType',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('referenced', new TypeReference($referencedType->reference)),
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
        ->path->toBe('types-a.d.ts')
        ->contents->toEqual(
            <<<TS
declare namespace App {
namespace Models {
export type ReferencedType = string;
}
}

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
        ->path->toBe('types-b.d.ts')
        ->contents->toEqual(
            <<<TS
export type LocalType = {
referenced: App.Models.ReferencedType
};

TS
        );
});

it('can reference types from a module writer', function () {
    $moduleWriter = new ModuleWriter(path: null);
    $flatModuleWriter = new FlatModuleWriter('flat-types.ts');
    $globalWriter = new GlobalNamespaceWriter('types.ts');

    $transformedCollection = new TransformedCollection([
        $moduleType = TransformedFactory::alias(
            name: 'ModuleType',
            typeScriptNode: new TypeScriptString(),
            location: ['models'],
            writer: $moduleWriter
        )->build(),
        $flatModuleType = TransformedFactory::alias(
            name: 'FlatModuleType',
            typeScriptNode: new TypeScriptString(),
            writer: $flatModuleWriter
        )->build(),
        $globalType = TransformedFactory::alias(
            name: 'GlobalType',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('module', new TypeReference($moduleType->reference)),
                new TypeScriptProperty('flatModule', new TypeReference($flatModuleType->reference)),
            ]),
            references: [$moduleType, $flatModuleType],
            writer: $globalWriter
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $globalFiles = $globalWriter->output(
        [$globalType],
        $transformedCollection,
    );

    expect($globalFiles)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($globalFiles[0])
        ->path->toBe('types.d.ts')
        ->contents->toEqual(
            <<<TS
import { ModuleType } from './models';
import { FlatModuleType } from './flat-types';
export type GlobalType = {
module: ModuleType
flatModule: FlatModuleType
};

TS
        );
});
