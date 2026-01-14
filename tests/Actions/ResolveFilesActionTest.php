<?php

use Spatie\TypeScriptTransformer\Actions\ResolveFilesAction;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Collections\WritersCollection;
use Spatie\TypeScriptTransformer\Support\WriteableFile;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

it('correctly divides transformed objects between writers', function () {
    $collection = new TransformedCollection();

    $writerA = new FlatModuleWriter('a.d.ts');
    $writerB = new FlatModuleWriter('b.d.ts');
    $writerC = new FlatModuleWriter('c.d.ts');

    $transformedA1 = transformSingle(new #[TypeScript] class () {
        public string $propertyA1;
    })->setWriter($writerA);

    $transformedA2 = transformSingle(new #[TypeScript] class () {
        public string $propertyA2;
    })->setWriter($writerA);

    $transformedB = transformSingle(new #[TypeScript] class () {
        public string $propertyB;
    })->setWriter($writerB);

    $transformedC = transformSingle(new #[TypeScript] class () {
        public string $propertyC;
    })->setWriter($writerC);

    $collection->add($transformedA1, $transformedB, $transformedA2, $transformedC);

    $writersCollection = new WritersCollection($writerA);
    $writersCollection->addStandaloneWriter($writerB);
    $writersCollection->addStandaloneWriter($writerC);

    $writeableFiles = (new ResolveFilesAction())->execute($collection, $writersCollection);

    expect($writeableFiles)
        ->toBeArray()
        ->toHaveCount(3);

    expect($writeableFiles[0])
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe('a.d.ts')
        ->contents->toContain('propertyA1')
        ->contents->toContain('propertyA2')
        ->contents->not->toContain('propertyB')
        ->contents->not->toContain('propertyC');

    expect($writeableFiles[1])
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe('b.d.ts')
        ->contents->toContain('propertyB')
        ->contents->not->toContain('propertyA1')
        ->contents->not->toContain('propertyA2')
        ->contents->not->toContain('propertyC');

    expect($writeableFiles[2])
        ->toBeInstanceOf(WriteableFile::class)
        ->path->toBe('c.d.ts')
        ->contents->toContain('propertyC')
        ->contents->not->toContain('propertyA1')
        ->contents->not->toContain('propertyA2')
        ->contents->not->toContain('propertyB');
});
