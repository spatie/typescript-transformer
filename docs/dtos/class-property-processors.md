---
title: Class property processors
weight: 3
---

Class property processors can be added to a `DtoTransformer`. They can change the types of class properties.

## Default class property processors

In the default package we provide three processors:

- `ReplaceDefaultTypesClassPropertyProcessor` replaces some types defined in the configuration, this processor will always run first
- `ApplyNeverClassPropertyProcessor` when a property is not well-typed, `never` is used as Typescript type, so you know your properties can be better typed
- `DtoCollectionClassPropertyProcessor` replaces `DtoCollections` from the `spatie/data-transfer-object` package with their Typescript equivalents

Specifically for Laravel, we include the following processors in the Laravel package:

- `LaravelCollectionClassPropertyProcessor` since Laravel has a `Collection` type which is an `array` we can replace

## Writing class property processors

A class property processor is a class that implements `ClassPropertyProcessor`:

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

- **type**: a [PHPDocumenter](https://www.phpdoc.org) type that describes the property's type
- **reflection**: the `ReflectionProperty` of the property

You should return a [PHPDocumenter](https://www.phpdoc.org) type or `null` if you want to remove the type from the Dto.

### Returning types

You can return whatever type you want. If you want to return a Typescript specific type, you can return a `TypeScriptType`. The following transformer, for example, will convert each property type into a `string`:

```php
class MyClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(Type $type, ReflectionProperty $reflection): ?Type
    {
        return TypescriptType::create('SomeGenericType<string>');
    }
}
```

Or you could also return a PHPDocumenter type:

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

Since a type can exist of arrays, compound types, nullable types, and more, you sometimes want to walk over these types. This can be done by including the `ProcessesClassProperties` trait into your ClassPropertyProcessor.

This trait will add a `walk` function that takes an initial type and closure.

Let's say you have a compound type like `string|bool|int`. The `walk` function will run a string, bool and int type through the closure. You can return whatever type you want. In the end, the updated compound type will also be passed to the closure. When you return `null`, the type will be removed. Let's take a look at an example where we only keep the string types and remove the others:

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

Why checking for the compound type? In the end, the compound type will be given to the closure. If we removed it, the whole property could be removed from the Typescript definition.


Do not forget you have to create your own `DtoTransformer` with your class property processors in the `getClassPropertyProcessors` method.
