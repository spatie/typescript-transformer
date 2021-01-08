---
title: Changing types using type processors
weight: 3
---

Type processors can be used to change the types of class property types, method parameters types or method return types for a `Transformer`.

## Default type processors

In the default package we provide 3 processors:

- `ReplaceDefaultsTypeProcessor` replaces some types defined in the configuration, this processor will always run first
- `DtoCollectionTypeProcessor` replaces `DtoCollections` from the `spatie/data-transfer-object` package with their TypeScript equivalent

Specifically for Laravel, we also include the following processors in the Laravel package:

- `LaravelCollectionTypeProcessor` handles Laravel's `Collection` classes like `array`s

## Writing class property processors

A class property processor is any class that implements the `ClassPropertyProcessor` interface:

```php
class MyClassPropertyProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection
    ): ?Type
    {
        // Transform the types of the property
    }
}
```

The `process` method has two parameters:

- `Type $type`: a [PHPDocumenter](https://www.phpdoc.org) type that describes the property's type
- `ReflectionProperty|ReflectionParameter|ReflectionMethod` $reflection when processing:
    - a type of a class property this will be a `ReflectionProperty`
    - a type of a method parameter this will be a `ReflectionParameter`
    - the return type of a method this will be a `ReflectionMethod`
    
You should return a [PHPDocumenter](https://www.phpdoc.org) type or `null` if you want to remove the type from the transformer.

### Returning types

You can return either a PHPDocumenter type or a `TypeScriptType` instance for TypeScript specific types. 

Lets take a look at an example, with this type processor, each property type will be converted into a `string`.

Using `TypeScriptType`:

```php
class MyClassPropertyProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection
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
        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection
    ): ?Type
    {
        return new String_();
    }
}
```

You can find all the possible PHPDocumenter types [here](https://github.com/phpDocumentor/TypeResolver/tree/1.x/src/Types).


### Walking over types

Since any type can exist of arrays, compound types, nullable types, and more, you'll sometimes need to walk (or loop) over these types to specify types case by case. This can be done by including the `ProcessesTypes` trait into your type processor.

This trait will add a `walk` method that takes an initial type and closure.

Let's say you have a compound type like `string|bool|int`. The `walk` method will run a `string`, `bool` and `int` type through the closure. You can then decide a type to be returned for each type given to the closure. Finally, the updated compound type will also be passed to the closure. 

Removing types can still be done by returning `null`. 

Let's take a look at an example where we only keep `string` types and remove any others:

```php
class MyClassPropertyProcessor implements ClassPropertyProcessor
{
    use ProcessesTypes;

    public function process(Type $type, ReflectionProperty $reflection): ?Type
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

As you can see, we check in the closure if the type is a `string` or a `compound` type. If it is none of these two types, we remove it by returning `null`. 

Why checking if the given type is a compound type? In the end, the compound type will be given to the closure. If we removed it, the whole property could be removed from the TypeScript definition.

Do not forget you have to create your own `DtoTransformer` with your class property processors in the `getTypeProcessors` method.
