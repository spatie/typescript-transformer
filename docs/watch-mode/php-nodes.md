---
title: PHP Nodes
weight: 4
---

TypeScript transformer can keep track of PHP classes, wether it is for transforming a class to TypeScript or for other purposes like inspecting the class for references to other classes.

Within the package we collect all these classes into the `PhpNodeCollection` which is a persistent registry of `PhpClassNode` objects, keyed by their fully qualified class name (FQCN). This collection keeps PHP reflection data available throughout the transformer's lifecycle, including watch mode.

When a class changes during watch mode, the `PhpNodeCollection` automatically updates the corresponding `PhpClassNode` with the new reflection data.

The collection can be injected into a `TransformedProvider` as such:

```php
use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\TransformedProviders\PhpNodesAwareTransformedProvider;

class ControllerScanningProvider implements TransformedProvider, PhpNodesAwareTransformedProvider
{
    private PhpNodeCollection $phpNodeCollection;

    public function setPhpNodeCollection(PhpNodeCollection $phpNodeCollection): void
    {
        $this->phpNodeCollection = $phpNodeCollection;
    }

    public function provide(): array
    {
        // You now can use $this->phpNodeCollection to access the PHP class nodes
    }
}
````

The collection will not store all PHP classes it encounters, it will keep track of:

- PHP classes/interfaces/enums which were transformed by a transformer
- Manually added PHP classes/interfaces/enums by providers

That's why on the initial run of the provider, the collection will be empty when the transformers haven't run yet.

In order to add new nodes to the collection you'll need to load them, the package provides a `LoadPhpClassNodeAction` for this purpose, you'll can inject this action into your provider by implementing the `ActionAwareTransformedProvider` interface (see above).

Please note in order to be able to load a PHP class node from a file, the file should contain exactly one class, interface or enum. When the file contains zero or multiple classes, the action will return null since it doesn't know which class to load.

It is possible to add nodes to the collection like this:

```php
$this->phpNodeCollection->add($phpClassNode);
```

You can check if a node exists in the collection by its FQCN:

```php
$this->phpNodeCollection->has($fqcn);
```

And het a node as such:

```php
$node = $this->phpNodeCollection->get($fqcn);
```
