---
title: Visiting TypeScript nodes
weight: 3
---

When working with TypeScript nodes in a class property processor or a custom TypeScript node, it might be necessary to
visit and alter nodes in the tree. The `Visitor` class can be used to visit such a tree of TypeScript
nodes.

The visitor will start in a node and then traverse the tree of TypeScript nodes, it is possible to register a `before`
and `after` callback for each node it visits. The `before` callback is called before visiting the children of a node and
the `after` callback is called after visiting the children of a node.

```php
use Spatie\TypeScriptTransformer\Visitor\Visitor;

Visitor::create()
    ->before(function (TypeScriptNode $node){
        echo 'Before visiting ' . $node::class . PHP_EOL;
    })
    ->after(function (TypeScriptNode $node) {
        echo 'After visiting ' . $node::class . PHP_EOL;
    })
    ->execute($rootNode);
```

When running the visitor on the following node:

```php
$rootNode = new TypeScriptUnion([
    new TypeScriptString(),
    new TypeScriptNumber(),
]);
```

The output will be (redacted for readability):

```
Before visiting TypeScriptUnion
Before visiting TypeScriptString
After visiting TypeScriptString
Before visiting TypeScriptNumber
After visiting TypeScriptNumber
After visiting TypeScriptUnion
```

By default, the visitor will visit the tree of nodes and run the callback on each node within the tree. It is possible
to limit the types of nodes the callback runs on:

```php
Visitor::create()
    ->after(function (TypeScriptUnion $node, [TypeScriptUnion::class]) {
        // Do something with TypeScriptUnion nodes
    })
    ->execute($rootNode);
```

When not returning a TypeScript node from the callback, the visitor will continue traversing the tree. It is possible to
replace a node in the tree like this:

```php
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

Visitor::create()
    ->after(function (TypeScriptUnion $node, [TypeScriptUnion::class]) {
        if(count($node->types) === 1) {
            return VisitorOperation::replace(array_values($node->types)[0]);
        }
    })
    ->execute($rootNode);
```

The visitor above will replace all union nodes with a single type with that type.

It is also possible to remove a node from the tree:

```php
Visitor::create()
    ->after(function (TypeScriptString $node, [TypeScriptString::class]) {
        return VisitorOperation::remove();
    })
    ->execute($rootNode);
```

## Hooking into TypeScript transformer

Every time the TypeScript transformer is executed, it will go through a series of steps, it is possible to run a visitor
in between some of these steps.

The steps look as following:

1. Running of the TransformedProviders creating a collection of Transformed objects
2. Running other providers to add extra Transformed objects
3. Possible hooking point: `providedVisitorHook`
4. Connecting references between Transformed objects
5. Possible hooking point: `connectedVisitorHook`
6. Create a collection of WriteableFiles
7. Write those files to disk
8. Format the files

The two hooking points below can be used to run a visitor on the collection of Transformed objects:

```php
use Spatie\TypeScriptTransformer\Visitor\VisitorClosureType;

$config->providedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::Before
);

$config->connectedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::Before
);
```

Running visitors as an after hook is also possible:

```php
$config->providedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::After
);

$config->connectedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::After
);
```
