---
title: Building your own TypeScript node
weight: 2
---

The package comes with a lot of TypeScript nodes, but sometimes it might be necessary to build your own.

A TypeScript node is a regular PHP class that implements the `TypeScriptNode` interface:

```php
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class PickNode implements TypeScriptNode, TypeScriptNamedNode
{
    public function __construct(
        private TypeScriptNode $type,
        private array $properties,
    ) {}

    public function write(WritingContext $context): string
    {
        return 'Pick<' . $this->type->write($context) . ', ' . implode(' | ', $this->properties) . '>';
    }

    public function getName(): string
    {
        return 'Pick';
    }
}
```

The write method is responsible for transforming the TypeScript node to a string, the `WritingContext` object is passed
to lower level TypeScript nodes to reference other TypeScript types and can generally be ignored.

Some TypeScript nodes represent a type with a name like an interface, enum, ... these nodes should implement
the `TypeScriptNamedNode` interface. The `getName` method should return the name of the TypeScript node so that it can
be referenced by other TypeScript nodes.

When you've got a node which itself contains another TypeScript node that can be a `TypeScriptNamedNode` we recommend
you to implement `TypeScriptForwardingNamedNode`. This interface requires you to implement the `getForwardedNamedNode`
method which should return the TypeScript node that either is another `TypeScriptForwardingNamedNode`
or `TypeScriptNamedNode`. An example of such a node is the `TypeScriptAlias`:

```php
class TypeScriptAlias implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    public function __construct(
        public TypeScriptIdentifier|TypeScriptGeneric $identifier,
        public TypeScriptNode $type,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "type {$this->identifier->write($context)} = {$this->type->write($context)};";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
```

Lastly, the package also provides some tooling to visit a tree of all TypeScript nodes. When your custom node
encapsulates other TypeScript nodes, you should mark those properties with the `#[NodeVisitable]` attribute so the
visitor knows to traverse them. This works for both single node properties and arrays of nodes:

```php
use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;

class TypeScriptGeneric implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    /**
     * @param  array<TypeScriptNode>  $genericTypes
     */
    public function __construct(
        #[NodeVisitable]
        public TypeScriptIdentifier|TypeScriptReference $type,
        #[NodeVisitable]
        public array $genericTypes,
    ) {
    }

    // ....
}
```
