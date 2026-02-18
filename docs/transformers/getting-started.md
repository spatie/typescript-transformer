---
title: Getting started
weight: 1
---

Transformers are the most important part of TypeScript transformer, they take a PHP class and try to transform it to a
TypeScript type. A transformer implements the `Transformer` interface:

```php
interface Transformer
{
    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable;
}
```

The `TransformationContext` contains all the information you need to transform a class:

```php
class TransformationContext
{
    public function __construct(
        // The name for the class that is being transformed, can be user defined
        public string $name,
        // The segments of the namespace where the class is located
        public array $nameSpaceSegments,
    ) {
    }
}
```

Within the method a `Transformed` data object should be created and returned which looks like this:

```php
use Spatie\TypeScriptTransformer\References\ClassStringReference;

new Transformed(
    // The TypeScript node representing the transformed class
    typeScriptNode: $typeScriptNode,
    // A unique name for the transformed class for internal package use
    reference: new ClassStringReference($reflectionClass->getName()),
    // A location where the class should be written to
    // By default, this is the namespace of the class and the $nameSpaceSegments from the TransformationContext can be used
    location: $context->nameSpaceSegments,
    // Whether the type should be exported in TypeScript
    export: true,
);
```

If a class cannot be transformed, the `Untransformable` object should be returned:

```php
use Spatie\TypeScriptTransformer\Untransformable;

Untransformable::create();
```

When a class cannot be transformed, the next transformer in the list will be executed.
