<?php

use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;
use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\TestSupport\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
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
            typeScriptNode: new TypeScriptReference($reference->getReference()),
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

    $referenceKey = $reference->getReference()->getKey();

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
        $nestedReference->getReference()->getKey() => 'Nested',
        $parentReference->getReference()->getKey() => 'Parent',
        $deeperParent->getReference()->getKey() => 'DeeperParent',
        $rootReference->getReference()->getKey() => 'Root',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedReference->getReference()->getKey() => [
                    'name' => 'Nested',
                    'reference' => $nestedReference->getReference()->getKey(),
                    'alias' => null,
                ],
            ],
        ],
        '../' => [
            'path' => '../',
            'segments' => [
                $parentReference->getReference()->getKey() => [
                    'name' => 'Parent',
                    'reference' => $parentReference->getReference()->getKey(),
                    'alias' => null,
                ],
            ],
        ],
        '../deeper' => [
            'path' => '../deeper',
            'segments' => [
                $deeperParent->getReference()->getKey() => [
                    'name' => 'DeeperParent',
                    'reference' => $deeperParent->getReference()->getKey(),
                    'alias' => null,
                ],
            ],
        ],
        '../../' => [
            'path' => '../../',
            'segments' => [
                $rootReference->getReference()->getKey() => [
                    'name' => 'Root',
                    'reference' => $rootReference->getReference()->getKey(),
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
        $nestedReference->getReference()->getKey() => 'Nested',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedReference->getReference()->getKey() => [
                    'name' => 'Nested',
                    'reference' => $nestedReference->getReference()->getKey(),
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
        $nestedCollection->getReference()->getKey() => 'CollectionImport',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedCollection->getReference()->getKey() => [
                    'name' => 'Collection',
                    'reference' => $nestedCollection->getReference()->getKey(),
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
        $nestedCollection->getReference()->getKey() => 'CollectionImport',
        $otherNestedCollection->getReference()->getKey() => 'CollectionImport2',
    ]);

    expect($imports->getImports())->toEqual([
        './nested' => [
            'path' => './nested',
            'segments' => [
                $nestedCollection->getReference()->getKey() => [
                    'name' => 'Collection',
                    'reference' => $nestedCollection->getReference()->getKey(),
                    'alias' => 'CollectionImport',
                ],
            ],
        ],
        './otherNested' => [
            'path' => './otherNested',
            'segments' => [
                $otherNestedCollection->getReference()->getKey() => [
                    'name' => 'Collection',
                    'reference' => $otherNestedCollection->getReference()->getKey(),
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
        $globalReference->getReference()->getKey() => 'App.Models.GlobalType',
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
        $nonExportedReference->getReference()->getKey() => 'NonExported',
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
        $nonExportedGlobalReference->getReference()->getKey() => 'App.Models.NonExportedGlobal',
    ]);
    expect($imports->getImports())->toBeEmpty();
});

it('will import an additional import from another path', function () {
    $import = new AdditionalImport('types/components.ts', 'SomeComponent');

    $transformed = TransformedFactory::alias(
        name: 'LocalType',
        typeScriptNode: new TypeScriptString(),
        writer: $this->writer,
        additionalImports: [$import],
    )->build();

    $transformedCollection = new TransformedCollection([$transformed]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [$transformed],
        $transformedCollection
    );

    $referenceKey = $import->getReferenceKeys()['SomeComponent'];

    expect($referenceMap)->toHaveKey($referenceKey);
    expect($referenceMap[$referenceKey])->toBe('SomeComponent');

    expect($imports->getImports())->toEqual([
        './types/components' => [
            'path' => './types/components',
            'segments' => [
                $referenceKey => [
                    'name' => 'SomeComponent',
                    'reference' => $referenceKey,
                    'alias' => null,
                ],
            ],
        ],
    ]);
});

it('will add an additional import to the reference map when in the same file', function () {
    $import = new AdditionalImport('index.ts', 'SomeComponent');

    $transformed = TransformedFactory::alias(
        name: 'LocalType',
        typeScriptNode: new TypeScriptString(),
        writer: $this->writer,
        additionalImports: [$import],
    )->build();

    $transformedCollection = new TransformedCollection([$transformed]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [$transformed],
        $transformedCollection
    );

    $referenceKey = $import->getReferenceKeys()['SomeComponent'];

    expect($referenceMap)->toHaveKey($referenceKey);
    expect($referenceMap[$referenceKey])->toBe('SomeComponent');
    expect($imports->getImports())->toBeEmpty();
});

it('will alias an additional import if the name conflicts with a type in the module', function () {
    $import = new AdditionalImport('types/components.ts', 'Foo');

    $transformed = TransformedFactory::alias(
        name: 'Foo',
        typeScriptNode: new TypeScriptString(),
        writer: $this->writer,
        additionalImports: [$import],
    )->build();

    $transformedCollection = new TransformedCollection([$transformed]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [$transformed],
        $transformedCollection
    );

    $referenceKey = $import->getReferenceKeys()['Foo'];

    expect($referenceMap)->toHaveKey($referenceKey);
    expect($referenceMap[$referenceKey])->toBe('FooImport');

    expect($imports->getImports())->toEqual([
        './types/components' => [
            'path' => './types/components',
            'segments' => [
                $referenceKey => [
                    'name' => 'Foo',
                    'reference' => $referenceKey,
                    'alias' => 'FooImport',
                ],
            ],
        ],
    ]);
});

it('will not duplicate an additional import', function () {
    $import = new AdditionalImport('types/components.ts', 'SomeComponent');

    $transformedA = TransformedFactory::alias(
        name: 'TypeA',
        typeScriptNode: new TypeScriptString(),
        writer: $this->writer,
        additionalImports: [$import],
    )->build();

    $transformedB = TransformedFactory::alias(
        name: 'TypeB',
        typeScriptNode: new TypeScriptString(),
        writer: $this->writer,
        additionalImports: [$import],
    )->build();

    $transformedCollection = new TransformedCollection([$transformedA, $transformedB]);

    [$imports, $referenceMap] = $this->action->execute(
        'index.ts',
        [$transformedA, $transformedB],
        $transformedCollection
    );

    $referenceKey = $import->getReferenceKeys()['SomeComponent'];

    expect($referenceMap)->toHaveKey($referenceKey);
    expect($referenceMap[$referenceKey])->toBe('SomeComponent');

    expect($imports->getImports())->toEqual([
        './types/components' => [
            'path' => './types/components',
            'segments' => [
                $referenceKey => [
                    'name' => 'SomeComponent',
                    'reference' => $referenceKey,
                    'alias' => null,
                ],
            ],
        ],
    ]);
});
