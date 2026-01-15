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

beforeEach(function () {
    $this->writer = new ModuleWriter(path: null);
});

it('can write modules', function () {
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

    $files = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($files[0])
        ->path->toBe('index.ts')
        ->contents->toBe('export type RootType = string;'.PHP_EOL.'export type RootType2 = string;'.PHP_EOL);

    expect($files[1])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['level1', 'index.ts']))
        ->contents->toBe('export type Level1Type = string;'.PHP_EOL.'export type Level1Type2 = string;'.PHP_EOL);

    expect($files[2])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['level1', 'level2', 'index.ts']))
        ->contents->toBe('export type Level2Type = string;'.PHP_EOL);
});

it('can customize the module filename', function () {
    $customWriter = new ModuleWriter(path: null, moduleFilename: 'custom.ts');

    $rootTransformed = TransformedFactory::alias(
        name: 'Type',
        typeScriptNode: new TypeScriptString(),
        writer: $customWriter
    )->build();
    $nestedTransformed = TransformedFactory::alias(
        name: 'Type',
        typeScriptNode: new TypeScriptString(),
        location: ['nested'],
        writer: $customWriter
    )->build();

    $transformedCollection = new TransformedCollection([$rootTransformed, $nestedTransformed]);

    $files = $customWriter->output($transformedCollection->all(), $transformedCollection);

    expect($files[0]->path)->toBe('custom.ts');
    expect($files[1]->path)->toBe(implode(DIRECTORY_SEPARATOR, ['nested', 'custom.ts']));
});

it('can reference other types within the module', function () {
    $reference = new CustomReference('test', 'A');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeScriptString(),
            reference: $reference,
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'B',
            typeScriptNode: new TypeReference($reference),
            writer: $this->writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $files = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($files[0])
        ->path->toBe('index.ts')
        ->contents->toBe('export type A = string;'.PHP_EOL.'export type B = A;'.PHP_EOL);
});

it('can reference other types within a nested module', function () {
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
                new TypeScriptProperty('a', new TypeReference($referenceA)),
                new TypeScriptProperty('b', new TypeReference($referenceB)),
            ]),
            writer: $this->writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $files = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($files[0])
        ->path->toBe('index.ts')
        ->contents->toBe(
            <<<'TypeScript'
import { A } from './nested';
import { B } from './nested/subNested';
export type C = {
a: A
b: B
};

TypeScript
        );

    expect($files[1])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['nested', 'index.ts']))
        ->contents->toBe('export type A = string;'.PHP_EOL);

    expect($files[2])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['nested', 'subNested', 'index.ts']))
        ->contents->toBe('export type B = string;'.PHP_EOL);
});

it('can combine imports from nested modules', function () {
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
            location: ['nested'],
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'C',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('a', new TypeReference($referenceA)),
                new TypeScriptProperty('b', new TypeReference($referenceB)),
            ]),
            writer: $this->writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $files = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($files[0])
        ->path->toBe('index.ts')
        ->contents->toBe(
            <<<'TypeScript'
import { A, B } from './nested';
export type C = {
a: A
b: B
};

TypeScript
        );

    expect($files[1])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['nested', 'index.ts']))
        ->contents->toBe('export type A = string;'.PHP_EOL.'export type B = string;'.PHP_EOL);
});

it('can import from root into a nested module', function () {
    $reference = new CustomReference('test', 'A');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeScriptString(),
            reference: $reference,
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'B',
            typeScriptNode: new TypeReference($reference),
            location: ['nested'],
            writer: $this->writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $files = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($files[0])
        ->path->toBe('index.ts')
        ->contents->toBe('export type A = string;'.PHP_EOL);

    expect($files[1])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['nested', 'index.ts']))
        ->contents->toBe(<<<'TypeScript'
import { A } from '../';
export type B = A;

TypeScript);
});

it('can automatically alias imported types', function () {
    $reference = new CustomReference('test', 'A');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeScriptString(),
            reference: $reference,
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeReference($reference),
            location: ['nested'],
            writer: $this->writer
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $files = $this->writer->output(
        $transformedCollection->all(),
        $transformedCollection,
    );

    expect($files)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($files[0])
        ->path->toBe('index.ts')
        ->contents->toBe('export type A = string;'.PHP_EOL);

    expect($files[1])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['nested', 'index.ts']))
        ->contents->toBe(<<<'TypeScript'
import { A as AImport } from '../';
export type A = AImport;

TypeScript);
});

it('can reference types from another module writer', function () {
    $writerA = new ModuleWriter(path: null);
    $writerB = new ModuleWriter(path: 'prefixed');

    $transformedCollection = new TransformedCollection([
        $referencedType = TransformedFactory::alias(
            name: 'ReferencedType',
            typeScriptNode: new TypeScriptString(),
            location: ['models'],
            writer: $writerA
        )->build(),
        $localType = TransformedFactory::alias(
            name: 'LocalType',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('referenced', new TypeReference($referencedType->reference)),
            ]),
            references: [$referencedType],
            location: ['services'],
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
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['models', 'index.ts']))
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
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['prefixed', 'services', 'index.ts']))
        ->contents->toEqual(
            <<<TS
import { ReferencedType } from '../../models';
export type LocalType = {
referenced: ReferencedType
};

TS
        );
});

it('can reference types from a flat module writer and global namespace writer', function () {
    $moduleWriter = new ModuleWriter(path: null);
    $flatWriter = new FlatModuleWriter('flat-types.ts');
    $globalWriter = new GlobalNamespaceWriter('global-types.ts');

    $transformedCollection = new TransformedCollection([
        $flatType = TransformedFactory::alias(
            name: 'FlatType',
            typeScriptNode: new TypeScriptString(),
            writer: $flatWriter
        )->build(),
        $globalType = TransformedFactory::alias(
            name: 'GlobalType',
            typeScriptNode: new TypeScriptString(),
            location: ['App', 'Models'],
            writer: $globalWriter
        )->build(),
        $moduleType = TransformedFactory::alias(
            name: 'ModuleType',
            typeScriptNode: new TypeScriptObject([
                new TypeScriptProperty('flat', new TypeReference($flatType->reference)),
                new TypeScriptProperty('global', new TypeReference($globalType->reference)),
            ]),
            references: [$flatType, $globalType],
            location: ['services'],
            writer: $moduleWriter
        )->build(),
    ]);

    (new ConnectReferencesAction(new NullLogger()))->execute($transformedCollection);

    $moduleFiles = $moduleWriter->output(
        [$moduleType],
        $transformedCollection,
    );

    expect($moduleFiles)
        ->toHaveCount(1)
        ->each->toBeInstanceOf(WriteableFile::class);

    expect($moduleFiles[0])
        ->path->toBe(implode(DIRECTORY_SEPARATOR, ['services', 'index.ts']))
        ->contents->toEqual(
            <<<TS
import { FlatType } from '../flat-types';
export type ModuleType = {
flat: FlatType
global: App.Models.GlobalType
};

TS
        );
});
