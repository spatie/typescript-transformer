---
title: Custom writers
weight: 4
---

It is possible to create your own writer by implementing the `Writer` interface:

```php
use Spatie\TypeScriptTransformer\Data\WriteableFile;

interface Writer
{
/**
     * @param array<Transformed> $transformed
     *
     * @return array<WriteableFile>
     */
    public function output(
        array $transformed,
        TransformedCollection $transformedCollection,
    ): array;

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference;
}
```

The `output` method should return an array of `WriteableFile` objects, these objects contain the TypeScript
code and the path relative to the configured output directory where the file should be stored.

The parameters for output are an array of `Transformed` objects and the full `TransformedCollection` while intuitively
these structures seem similar, the array of `Transformed` objects only contains the objects that need to be written by
this writer, while the`TransformedCollection` contains all transformed objects being handled by the package. This comes
in handy when needing to resolve references to other transformed objects not being written by this writer.

In order to reference to other transformed objects you'll need an identifier to be used within the TypeScript code. For
the GlobalNamespaceWriter this will be a fully qualified name like 'App.Models.User' while for the ModuleWriter this
will be an import statement like `import { User } from '../models/User'` and then an identifier like `User`.

In order to resolve these references we strongly recommend you to use the `ResolveImportsAndResolvedReferenceMapAction`
which takes the current path the writer is currently writing to, the array of transformed objects to be written by
this writer in the current file and the full transformed collection:

```php
use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;

[$imports, $resolvedReferenceMap] = (new ResolveImportsAndResolvedReferenceMapAction())->execute(
    currentPath: $currentPath, // e.g. 'app/models/index.ts'
    transformed: $transformed,
    transformedCollection: $transformedCollection,
);
```

The action returns a tuple containing, the collection of imports needed for the current file which can be transformed
into TypeScript import nodes like this:

```php
$imports->getTypeScriptNodes()
```

The action also returns a resolved reference map which is a mapping of the transformed reference key to their TypeScript
identifier, this map should be provided to the `WritingContext` a structure required for writing TypeScript nodes.

We now can write out the imports and transformed objects like this:

```php
$output = '';

foreach ($imports->getTypeScriptNodes() as $import) {
    $output .= $import->write($writingContext).PHP_EOL;
}

foreach ($location->transformed as $transformedItem) {
    $output .= $transformedItem->write($writingContext).PHP_EOL;
}

$writeableFile = new WriteableFile($filePath, $output);
```

In the end the writer then should return an array of all the `WriteableFile` objects it created.

## Configuring TypeScript identifier name resolution

The `ResolveImportsAndResolvedReferenceMapAction` uses a default strategy to resolve TypeScript identifiers from all the
transformed references. It works like this:

1. If the identifier was written by a GlobalNamespaceWriter, the fully qualified name is used as the identifier.
2. If no identifier in the module exists with the same name, the transformed name is used as the identifier.
3. If an identifier in the module already exists with the same name, a Import suffix is added to the transformed name.
4. If the Import suffix name also exists, a numeric suffix is added until a unique name is found.

It is possible to customize this strategy by providing a Closure returning a unique identifier for the current
identifier to be used:

```php
new ResolveImportsAndResolvedReferenceMapAction(
    moduleImportNameResolver: fn(string $identifier, array $usedIdentifiers): string => $identifier . uniqid(),
);
```

The first parameter is the identifier that would be used by default and the second parameter is an array of already used
identifiers within the current module. Every time the closure is called the array of used identifiers is updated with
the previously returned identifiers.
