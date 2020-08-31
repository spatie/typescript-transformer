---
title: Changing types using class property processors
weight: 3
---

Class property processors can be used to change the types of class properties for a `DtoTransformer`.

## Default class property processors

In the default package we provide 3 processors:

- `ReplaceDefaultTypesClassPropertyProcessor` replaces some types defined in the configuration, this processor will always run first
- `ApplyNeverClassPropertyProcessor` when a property is typed incorrectly, `never` is used as TypeScript type to indicate a property that should be typed better
- `DtoCollectionClassPropertyProcessor` replaces `DtoCollections` from the `spatie/data-transfer-object` package with their TypeScript equivalent

Specifically for Laravel, we also include the following processors in the Laravel package:

- `LaravelCollectionClassPropertyProcessor` handles Laravel's `Collection` classes like `array`s

## Writing class property processors

A class property processor is any class that implements the `ClassPropertyProcessor` interface:

```php
class MyClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(Type $type, ReflectionProperty $reflection): ?Type
    {
        // Transform the types of the property
    }
}
```

The `process` method has two parameters:

- `Type $type`: a [PHPDocumenter](https://www.phpdoc.org) type that describes the property's type
- `ReflectionProperty $reflection`: the `ReflectionProperty` of the property

You should return a [PHPDocumenter](https://www.phpdoc.org) type or `null` if you want to remove the type from the DTO.

### Returning types

You can return either a PHPDocumenter type or a `TypeScriptType` instance for TypeScript specific types. In a later step of generting the TypeScript definition, each property type will be converted into a `string`.

Using `TypeScriptType`:

```php
class MyClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(Type $type, ReflectionProperty $reflection): ?Type
    {
        return TypeScriptType::create('SomeGenericType<string>');
    }
}
```

Or using a PHPDocumenter type:

```php
class MyClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(Type $type, ReflectionProperty $reflection): ?Type
    {
        return new String_();
    }
}
```

You can find all the possible PHPDocumenter types [here](https://github.com/phpDocumentor/TypeResolver/tree/1.x/src/Types).


### Walking over types

Since any type can exist of arrays, compound types, nullable types, and more, you'll sometimes need to walk (or loop) over these types to specify types case by case. This can be done by including the `ProcessesClassProperties` trait into your ClassPropertyProcessor.

This trait will add a `walk` method that takes an initial type and closure.

Let's say you have a compound type like `string|bool|int`. The `walk` method will run a `string`, `bool` and `int` type through the closure. You can then decide a. type to be returned per type. Finally, the updated compound type will also be passed to the closure. This gives you the opportunity to remove the type by returning `null`. 

Let's take a look at an example where we only keep `string` types and remove any others:

```php
class MyClassPropertyProcessor implements ClassPropertyProcessor
{
    use ProcessesClassProperties;

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

Why checking for the compound type? In the end, the compound type will be given to the closure. If we removed it, the whole property could be removed from the TypeScript definition.

Do not forget you have to create your own `DtoTransformer` with your class property processors in the `getClassPropertyProcessors` method.
