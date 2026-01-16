<?php

use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;

beforeEach(function () {
    $this->action = new ResolveImportsAndResolvedReferenceMapAction();
    $this->writer = new ModuleWriter(path: null);
});

it('wont resolve imports when types are in the same module', function () {
    $transformedCollection = new TransformedCollection([
        $reference = TransformedFactory::alias(
            name: 'A',
            typeScriptNode: new TypeScriptString(),
            writer: $this->writer,
        )->build(),
        TransformedFactory::alias(
            name: 'B',
            typeScriptNode: new TypeReference($reference->reference),
            references: [
                $reference,
            ]
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        $transformedCollection->all(),
        $transformedCollection
    );

    $referenceKey = $reference->reference->getKey();

    expect($imports->getTypeScriptNodes())->toBeEmpty();
    expect($referenceMap)->toHaveKey($referenceKey);
    expect($referenceMap[$referenceKey])->toBe('A');
});

it('will import a type from another module', function () {
    $transformedCollection = new TransformedCollection([
        $nestedReference = TransformedFactory::alias(
            name: 'Nested',
            typeScriptNode: new TypeScriptString(),
            location: ['parent', 'level', 'nested'],
            writer: $this->writer
        )->build(),
        $parentReference = TransformedFactory::alias(
            name: 'Parent',
            typeScriptNode: new TypeScriptString(),
            location: ['parent'],
            writer: $this->writer
        )->build(),
        $deeperParent = TransformedFactory::alias(
            name: 'DeeperParent',
            typeScriptNode: new TypeScriptString(),
            location: ['parent', 'deeper'],
            writer: $this->writer
        )->build(),
        $rootReference = TransformedFactory::alias(
            name: 'Root',
            typeScriptNode: new TypeScriptString(),
            location: [],
            writer: $this->writer
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        implode(DIRECTORY_SEPARATOR, ['parent', 'level', 'index.ts']),
        [
            TransformedFactory::alias(
                name: 'Type',
                typeScriptNode: new TypeScriptString(),
                references: [
                    $nestedReference,
                    $parentReference,
                    $deeperParent,
                    $rootReference,
                ],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    expect($referenceMap)->toEqual([
        $nestedReference->reference->getKey() => 'Nested',
        $parentReference->reference->getKey() => 'Parent',
        $deeperParent->reference->getKey() => 'DeeperParent',
        $rootReference->reference->getKey() => 'Root',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedReference->reference->getKey() => [
                    'name' => 'Nested',
                    'reference' => $nestedReference->reference->getKey(),
                    'alias' => null,
                ],
            ],
        ],
        '../' => [
            'path' => '../',
            'segments' => [
                $parentReference->reference->getKey() => [
                    'name' => 'Parent',
                    'reference' => $parentReference->reference->getKey(),
                    'alias' => null,
                ],
            ],
        ],
        '../deeper' => [
            'path' => '../deeper',
            'segments' => [
                $deeperParent->reference->getKey() => [
                    'name' => 'DeeperParent',
                    'reference' => $deeperParent->reference->getKey(),
                    'alias' => null,
                ],
            ],
        ],
        '../../' => [
            'path' => '../../',
            'segments' => [
                $rootReference->reference->getKey() => [
                    'name' => 'Root',
                    'reference' => $rootReference->reference->getKey(),
                    'alias' => null,
                ],
            ],
        ],
    ]);
});

it('wont import the same type twice', function () {
    $transformedCollection = new TransformedCollection([
        $nestedReference = TransformedFactory::alias(
            name: 'Nested',
            typeScriptNode: new TypeScriptString(),
            location: ['nested'],
            writer: $this->writer
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [
            TransformedFactory::alias(
                name: 'TypeA',
                typeScriptNode: new TypeScriptString(),
                references: [$nestedReference],
                writer: $this->writer
            )->build(),
            TransformedFactory::alias(
                name: 'TypeB',
                typeScriptNode: new TypeScriptString(),
                references: [$nestedReference],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    expect($referenceMap)->toEqual([
        $nestedReference->reference->getKey() => 'Nested',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedReference->reference->getKey() => [
                    'name' => 'Nested',
                    'reference' => $nestedReference->reference->getKey(),
                    'alias' => null,
                ],
            ],
        ],
    ]);
});

it('will alias a reference if it is already in the module', function () {
    $transformedCollection = new TransformedCollection([
        $nestedCollection = TransformedFactory::alias(
            name: 'Collection',
            typeScriptNode: new TypeScriptString(),
            location: ['nested'],
            writer: $this->writer
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [
            TransformedFactory::alias(
                name: 'Collection',
                typeScriptNode: new TypeScriptString(),
                references: [$nestedCollection],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    expect($referenceMap)->toEqual([
        $nestedCollection->reference->getKey() => 'CollectionImport',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedCollection->reference->getKey() => [
                    'name' => 'Collection',
                    'reference' => $nestedCollection->reference->getKey(),
                    'alias' => 'CollectionImport',
                ],
            ],
        ],
    ]);
});

it('will alias a reference if it is already in the module and already aliased by another import', function () {
    $transformedCollection = new TransformedCollection([
        $nestedCollection = TransformedFactory::alias(
            name: 'Collection',
            typeScriptNode: new TypeScriptString(),
            location: ['nested'],
            writer: $this->writer
        )->build(),
        $otherNestedCollection = TransformedFactory::alias(
            name: 'Collection',
            typeScriptNode: new TypeScriptString(),
            location: ['otherNested'],
            writer: $this->writer
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [
            TransformedFactory::alias(
                name: 'Collection',
                typeScriptNode: new TypeScriptString(),
                references: [$nestedCollection, $otherNestedCollection],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    expect($referenceMap)->toEqual([
        $nestedCollection->reference->getKey() => 'CollectionImport',
        $otherNestedCollection->reference->getKey() => 'CollectionImport2',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedCollection->reference->getKey() => [
                    'name' => 'Collection',
                    'reference' => $nestedCollection->reference->getKey(),
                    'alias' => 'CollectionImport',
                ],
            ],
        ],
        './otherNested' => [
            'path' => './otherNested',
            'segments' => [
                $otherNestedCollection->reference->getKey() => [
                    'name' => 'Collection',
                    'reference' => $otherNestedCollection->reference->getKey(),
                    'alias' => 'CollectionImport2',
                ],
            ],
        ],
    ]);
});

it('will add global namespace references to the reference map but not import them', function () {
    $globalWriter = new GlobalNamespaceWriter();

    $transformedCollection = new TransformedCollection([
        $globalReference = TransformedFactory::alias(
            name: 'GlobalType',
            typeScriptNode: new TypeScriptString(),
            location: ['App', 'Models'],
            writer: $globalWriter
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [
            TransformedFactory::alias(
                name: 'LocalType',
                typeScriptNode: new TypeScriptString(),
                references: [$globalReference],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    expect($referenceMap)->toEqual([
        $globalReference->reference->getKey() => 'App.Models.GlobalType',
    ]);

    expect($imports->getImports())->toBeEmpty();
});

it('will not import a type that is not exported', function () {
    $transformedCollection = new TransformedCollection([
        $nonExportedReference = TransformedFactory::alias(
            name: 'NonExported',
            typeScriptNode: new TypeScriptString(),
            location: ['nested'],
            export: false,
            writer: $this->writer
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [
            TransformedFactory::alias(
                name: 'LocalType',
                typeScriptNode: new TypeScriptString(),
                references: [$nonExportedReference],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    expect($referenceMap)->toBeEmpty();
    expect($imports->getImports())->toBeEmpty();
});

it('will still resolve non-exported types within the same module', function () {
    $transformedCollection = new TransformedCollection([
        $nonExportedReference = TransformedFactory::alias(
            name: 'NonExported',
            typeScriptNode: new TypeScriptString(),
            export: false,
            writer: $this->writer
        )->build(),
        TransformedFactory::alias(
            name: 'LocalType',
            typeScriptNode: new TypeScriptString(),
            references: [$nonExportedReference],
            writer: $this->writer
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        $transformedCollection->all(),
        $transformedCollection
    );

    expect($referenceMap)->toEqual([
        $nonExportedReference->reference->getKey() => 'NonExported',
    ]);
    expect($imports->getImports())->toBeEmpty();
});

it('will still resolve non-exported types from a global namespace writer', function () {
    $globalWriter = new GlobalNamespaceWriter();

    $transformedCollection = new TransformedCollection([
        $nonExportedGlobalReference = TransformedFactory::alias(
            name: 'NonExportedGlobal',
            typeScriptNode: new TypeScriptString(),
            location: ['App', 'Models'],
            export: false,
            writer: $globalWriter
        )->build(),
    ]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [
            TransformedFactory::alias(
                name: 'LocalType',
                typeScriptNode: new TypeScriptString(),
                references: [$nonExportedGlobalReference],
                writer: $this->writer
            )->build(),
        ],
        $transformedCollection
    );

    // Global namespace types are always accessible (ambient declarations)
    expect($referenceMap)->toEqual([
        $nonExportedGlobalReference->reference->getKey() => 'App.Models.NonExportedGlobal',
    ]);
    expect($imports->getImports())->toBeEmpty();
});
