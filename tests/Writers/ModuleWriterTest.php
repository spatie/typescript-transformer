<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\Console\NullLogger;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

beforeEach(function () {
    $this->writer = new ModuleWriter();
});

it('can write modules', function () {
    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('RootType', new TypeScriptString())->build(),
        TransformedFactory::alias('RootType2', new TypeScriptString())->build(),
        TransformedFactory::alias('Level1Type', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level1Type2', new TypeScriptString(), location: ['level1'])->build(),
        TransformedFactory::alias('Level2Type', new TypeScriptString(), location: ['level1', 'level2'])->build(),
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
        ->path->toBe('level1/index.ts')
        ->contents->toBe('export type Level1Type = string;'.PHP_EOL.'export type Level1Type2 = string;'.PHP_EOL);

    expect($files[2])
        ->path->toBe('level1/level2/index.ts')
        ->contents->toBe('export type Level2Type = string;'.PHP_EOL);
});

it('can customize the module filename', function () {
    $rootTransformed = TransformedFactory::alias('Type', new TypeScriptString())->build();
    $nestedTransformed = TransformedFactory::alias('Type', new TypeScriptString(), location: ['nested'])->build();

    $customWriter = new ModuleWriter('custom.ts');

    $transformedCollection = new TransformedCollection([$rootTransformed, $nestedTransformed]);

    $files = $customWriter->output($transformedCollection->all(), $transformedCollection);

    expect($files[0]->path)->toBe('custom.ts');
    expect($files[1]->path)->toBe('nested/custom.ts');
});

it('can reference other types within the module', function () {
    $reference = new CustomReference('test', 'A');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('A', new TypeScriptString(), reference: $reference)->build(),
        TransformedFactory::alias('B', new TypeReference($reference))->build(),
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
        TransformedFactory::alias('A', new TypeScriptString(), reference: $referenceA, location: ['nested'])->build(),
        TransformedFactory::alias('B', new TypeScriptString(), reference: $referenceB, location: ['nested', 'subNested'])->build(),
        TransformedFactory::alias('C', new TypeScriptObject([
            new TypeScriptProperty('a', new TypeReference($referenceA)),
            new TypeScriptProperty('b', new TypeReference($referenceB)),
        ]))->build(),
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
import { A } from 'nested';
import { B } from 'nested/subNested';

export type C = {
a: A
b: B
};

TypeScript
        );

    expect($files[1])
        ->path->toBe('nested/index.ts')
        ->contents->toBe('export type A = string;'.PHP_EOL);

    expect($files[2])
        ->path->toBe('nested/subNested/index.ts')
        ->contents->toBe('export type B = string;'.PHP_EOL);
});

it('can combine imports from nested modules', function () {
    $referenceA = new CustomReference('test', 'A');
    $referenceB = new CustomReference('test', 'B');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('A', new TypeScriptString(), reference: $referenceA, location: ['nested'])->build(),
        TransformedFactory::alias('B', new TypeScriptString(), reference: $referenceB, location: ['nested'])->build(),
        TransformedFactory::alias('C', new TypeScriptObject([
            new TypeScriptProperty('a', new TypeReference($referenceA)),
            new TypeScriptProperty('b', new TypeReference($referenceB)),
        ]))->build(),
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
import { A, B } from 'nested';

export type C = {
a: A
b: B
};

TypeScript
        );

    expect($files[1])
        ->path->toBe('nested/index.ts')
        ->contents->toBe('export type A = string;'.PHP_EOL.'export type B = string;'.PHP_EOL);
});

it('can import from root into a nested module', function () {
    $reference = new CustomReference('test', 'A');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('A', new TypeScriptString(), reference: $reference)->build(),
        TransformedFactory::alias('B', new TypeReference($reference), location: ['nested'])->build(),
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
        ->path->toBe('nested/index.ts')
        ->contents->toBe(<<<'TypeScript'
import { A } from '../';

export type B = A;

TypeScript);
});

it('can automatically alias imported types', function () {
    $reference = new CustomReference('test', 'A');

    $transformedCollection = new TransformedCollection([
        TransformedFactory::alias('A', new TypeScriptString(), reference: $reference)->build(),
        TransformedFactory::alias('A', new TypeReference($reference), location: ['nested'])->build(),
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
        ->path->toBe('nested/index.ts')
        ->contents->toBe(<<<'TypeScript'
import { A as AImport } from '../';

export type A = AImport;

TypeScript);
});
