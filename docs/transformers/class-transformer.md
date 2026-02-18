---
title: Class transformer
weight: 2
---

Most of the time, transforming a class comes down to taking all the properties and transforming them to a TypeScript
object with properties, the package provides an easy-to-extend class for this called `ClassTransformer`.

You can create your own version by extending the `ClassTransformer` and implementing the `shouldTransform` method:

```php
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

class MyTransformer extends ClassTransformer
{
    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return $reflection->implementsInterface(\Spatie\LaravelData\Data::class);
    }
}
```

In the case above, the transformer will only run when transforming classes which are data objects from
the [laravel-data](https://github.com/spatie/laravel-data) package. We encourage you to overwrite certain methods so
that the transformer fits your needs.

## Choosing properties to transform

By default, all public non-static properties of a class are transformed, but you can overwrite the `properties` method
to change this:

```php
protected function getProperties(PhpClassNode $phpClassNode): array
{
    return $phpClassNode->getProperties(ReflectionProperty::IS_PUBLIC|ReflectionProperty::IS_PROTECTED);
}
```

## Optional properties

It is possible to make a property optional in TypeScript by overwriting the `isPropertyReadonly` method:

```php
protected function isPropertyOptional(
    PhpPropertyNode $phpPropertyNode,
    PhpClassNode $phpClassNode,
    TypeScriptNode $type,
    TransformationContext $context,
): bool {
    return str_starts_with($phpPropertyNode->getName(), '_');
}
```

By default, we check whether a property has an `#[Optional]` attribute.

## Readonly properties

You can make a property readonly by overwriting the `isPropertyReadonly` method:

```php
protected function isPropertyReadonly(
    PhpPropertyNode $phpPropertyNode,
    PhpClassNode $phpClassNode,
    TypeScriptNode $type,
): bool {
   return str_ends_with($phpPropertyNode->getName(), 'Read');
}
```

By default, we check whether a property was made readonly in PHP.

## Hiding properties

It is possible to completely hide a property from the TypeScript object by overwriting the `isPropertyHidden` method:

```php
protected function isPropertyHidden(
    PhpPropertyNode $phpPropertyNode,
    PhpClassNode $phpClassNode,
    TypeScriptProperty $property,
): bool {
    return count($phpPropertyNode->getAttributes(Hidden::class)) > 0;
}
```

By default, we check whether a property has an `#[Hidden]` attribute.

## Class property processors

Sometimes a more fine-grained control is needed over how a property is transformed, this is where class property
processors come to play. They allow you to update the TypeScript Node of the property, you can create them by
implementing the `ClassPropertyProcessor` interface:

```php
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;

class RemoveNullProcessor implements ClassPropertyProcessor
{
    public function execute(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        if ($property->type instanceof TypeScriptUnion) {
            $property->type = new TypeScriptUnion(
                array_values(array_filter($property->type->types, fn (TypeScriptNode $type) => !$type instanceof TypeScriptNull))
            );
        }

        return $property;
    }
}
```

You can add these processors to the transformer by overwriting the `classPropertyProcessors` method:

```php
protected function classPropertyProcessors(): array
{
    return [
        new RemoveNullProcessor(),
    ];
}
```

A class property processor can also be used to remove properties from the TypeScript object:

```php
class RemoveAllStrings implements ClassPropertyProcessor
{
    public function execute(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        if ($property->type instanceof TypeScriptString) {
            return null;
        }

        return $property;
    }
}
```

## Fixing array like structures

The package ships with `FixArrayLikeStructuresClassPropertyProcessor`, a built-in processor that converts generic array-like types into idiomatic TypeScript:

- `Array<int, string>` becomes `string[]`
- `Array<string, User>` becomes `Record<string, User>`
- `Collection<int, User>` becomes `User[]` (when registered)

You can register extra classes which behave like arrays as such

```php
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\FixArrayLikeStructuresClassPropertyProcessor;

protected function classPropertyProcessors(): array
{
    return [
        new FixArrayLikeStructuresClassPropertyProcessor()
            ->replaceArrayLikeClass(
                Illuminate\Support\Collection::class,
            ),
    ];
}
```

By default, it replaces `Array<K, V>` generics. This can be disabled by passing `replaceArrays: false` to the constructor.
