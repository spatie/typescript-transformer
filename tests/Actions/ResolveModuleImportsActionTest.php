<?php

use Spatie\TypeScriptTransformer\Actions\ResolveModuleImportsAction;
use Spatie\TypeScriptTransformer\Support\ImportLocation;
use Spatie\TypeScriptTransformer\Support\ImportName;
use Spatie\TypeScriptTransformer\Support\Location;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptImport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;

beforeEach(function () {
    $this->action = new ResolveModuleImportsAction();
});

it('wont resolve imports when types are in the same module', function () {
    $location = new Location([], [
        $reference = TransformedFactory::alias('A', new TypeScriptString())->build(),
        TransformedFactory::alias('B', new TypeReference($reference->reference), references: [
            $reference,
        ])->build(),
    ]);

    expect($this->action->execute($location)->isEmpty())->toBe(true);
});

it('will import a type from another module', function () {
    $nestedReference = TransformedFactory::alias('Nested', new TypeScriptString(), location: ['parent', 'level', 'nested'])->build();
    $parentReference = TransformedFactory::alias('Parent', new TypeScriptString(), location: ['parent'])->build();
    $deeperParent = TransformedFactory::alias('DeeperParent', new TypeScriptString(), location: ['parent', 'deeper'])->build();
    $rootReference = TransformedFactory::alias('Root', new TypeScriptString(), location: [])->build();

    $location = new Location(['parent', 'level'], [
        TransformedFactory::alias('Type', new TypeScriptString(), references: [
            $nestedReference,
            $parentReference,
            $deeperParent,
            $rootReference,
        ])->build(),
    ]);

    $imports = $this->action->execute($location);

    expect($imports->toArray())
        ->toHaveCount(4)
        ->each->toBeInstanceOf(ImportLocation::class);

    expect($imports->getTypeScriptNodes())->toEqual([
        new TypeScriptImport('nested', [new ImportName('Nested', $nestedReference->reference)]),
        new TypeScriptImport('../', [new ImportName('Parent', $parentReference->reference)]),
        new TypeScriptImport('../deeper', [new ImportName('DeeperParent', $deeperParent->reference)]),
        new TypeScriptImport('../../', [new ImportName('Root', $rootReference->reference)]),
    ]);
});

it('wont import the same type twice', function () {
    $nestedReference = TransformedFactory::alias('Nested', new TypeScriptString(), location: ['nested'])->build();

    $location = new Location([], [
        TransformedFactory::alias('TypeA', new TypeScriptString(), references: [
            $nestedReference,
        ])->build(),
        TransformedFactory::alias('TypeB', new TypeScriptString(), references: [
            $nestedReference,
        ])->build(),
    ]);

    $imports = $this->action->execute($location);

    expect($imports->toArray())
        ->toHaveCount(1)
        ->each->toBeInstanceOf(ImportLocation::class);

    expect($imports->getTypeScriptNodes())->toEqual([
        new TypeScriptImport('nested', [new ImportName('Nested', $nestedReference->reference)]),
    ]);
});

it('will alias a reference if it is already in the module', function (){
    $nestedCollection = TransformedFactory::alias('Collection', new TypeScriptString(), location: ['nested'])->build();

    $location = new Location([], [
        TransformedFactory::alias('Collection', new TypeScriptString(), references: [
            $nestedCollection,
        ])->build(),
    ]);

    $imports = $this->action->execute($location);

    expect($imports->toArray())
        ->toHaveCount(1)
        ->each->toBeInstanceOf(ImportLocation::class);

    expect($imports->getTypeScriptNodes())->toEqual([
        new TypeScriptImport('nested', [new ImportName('Collection', $nestedCollection->reference, 'CollectionImport')]),
    ]);
});

it('will alias a reference if it is already in the module and already aliased by another import', function (){
    $nestedCollection = TransformedFactory::alias('Collection', new TypeScriptString(), location: ['nested'])->build();
    $otherNestedCollection = TransformedFactory::alias('Collection', new TypeScriptString(), location: ['otherNested'])->build();

    $location = new Location([], [
        TransformedFactory::alias('Collection', new TypeScriptString(), references: [
            $nestedCollection,
            $otherNestedCollection,
        ])->build(),
    ]);

    $imports = $this->action->execute($location);

    expect($imports->toArray())
        ->toHaveCount(2)
        ->each->toBeInstanceOf(ImportLocation::class);

    expect($imports->getTypeScriptNodes())->toEqual([
        new TypeScriptImport('nested', [new ImportName('Collection', $nestedCollection->reference, 'CollectionImport')]),
        new TypeScriptImport('otherNested', [new ImportName('Collection', $otherNestedCollection->reference, 'CollectionImport2')]),
    ]);
});
