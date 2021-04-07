---
title: Type processors
weight: 3
---

You can use type processors to change an entity's internal `Type` before it is transpiled into TypeScript.

## Default type processors

- `ReplaceDefaultsTypeProcessor` replaces some types defined in the configuration
- `DtoCollectionTypeProcessor` replaces `DtoCollections` from the `spatie/data-transfer-object` package with their
  TypeScript equivalent

Specifically for Laravel, we also include the following type processors in the Laravel package:

- `LaravelCollectionTypeProcessor` handles Laravel's `Collection` classes like `array`s

## Using type processors in your transformers

When you're using the `TransformsTypes` [trait](https://github.com/spatie/typescript-transformer/blob/master/src/Transformers/TransformsTypes.php) in your transformer and use
the `reflectionToTypeScript` then you can additionally pass type processors:

```php
$this->reflectionToTypeScript(
    $reflection, 
    $missingSymbolsCollection,
    new ReplaceDefaultsTypeProcessor(),
    new DtoCollectionTypeProcessor(),
    // and so on ...
);
```

## Writing type processors

A class property processor is any class that implements the `ClassPropertyProcessor` interface:

```php
class MyClassPropertyProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type
    {
        // Transform the types of the property
    }
}
```

### Returning a type

You can either return a PHPDocumenter type or a `TypeScriptType` instance for literal TypeScript types.

Let's take a look at an example. With this type processor, it will convert each property type into a `string`.

Using a `TypeScriptType`:

```php
class MyClassPropertyProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type
    {
        return TypeScriptType::create('SomeGenericType<string>');
    }
}
```

Or using a PHPDocumenter type:

```php
class MyClassPropertyProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type
    {
        return new String_();
    }
}
```

You can find all the possible PHPDocumenter
types [here](https://github.com/phpDocumentor/TypeResolver/tree/1.x/src/Types).

### Walking over types

Since any type can exist of arrays, compound types, nullable types, and more, you'll sometimes need to walk (or loop)
over these types to specify types case by case. This can be done by including the `ProcessesTypes` trait into your type
processor.

This trait will add a `walk` method that takes an initial type and closure.

Let's say you have a compound type like `string|bool|int`. The `walk` method will run a `string`, `bool` and `int` type
through the closure. You can then decide a type to be returned for each type given to the closure. Finally, the updated
compound type will also be passed to the closure.

You can remove a type by returning `null`.

Let's take a look at an example where we only keep `string` types and remove any others:

```php
class MyClassPropertyProcessor implements TypeProcessor
{
    use ProcessesTypes;

    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type
    {
        return $this->walk($type, function (Type $type) {
            if ($type instanceof _String || $type instanceof Compound) {
                return $type;
            }

            return null;
        });
    }
}
```

As you can see, we check in the closure if the type is a `string` or a `compound` type. If it is none of these two
types, we remove it by returning `null`.

Why checking if the given type is a compound type? In the end, the compound type will be given to the closure. If we
removed it, the whole property could be removed from the TypeScript definition.
