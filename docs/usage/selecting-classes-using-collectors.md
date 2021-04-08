---
title: Collectors
weight: 5
---

In some cases, you'll want to transform classes without an attribute or annotation. For example, Laravel's API resources are almost always sent to the front and should always have a TypeScript definition ready to be used.

Collectors allow you to specify which PHP classes should be transformed to TypeScript and what transformer should be used.

The package comes out of the box with the pre-configured `DefaultCollector` to find and transform classes marked with the `@typescript` annotation and `#[TypeScript]` attributes.

A collector is a class that extends the abstract `Collector` class  and implements the `getTransformedType` method:

```php
class EnumCollector extends Collector
{
    public function getTransformedType(ReflectionClass $class): ?TransformedType
    {
    }
}
```

The `getTransformedType` will return a `TransformedType` object if it can transform a `ReflectionClass` to TypeScript. When not possible, the method should return `null`.

Don't forget to add the collector to your configuration:

```php
$config = TypeScriptTransformerConfig::create()
    ->collectors([EnumCollector::class])
   ...
```

Collectors will be checked in order if a perfect collector fit was found for a type. Then all the other collectors after that collector will be ignored for that type.

## Difference between Collectors and Transformers

Although the two concepts share a very similar interface, they are indeed different.

A collector takes Types and gives them to a specific transformer that the collector decided. For example, the `DefaultCollector` will run a type through each transformer you've configured to find the right one.

Collectors can also change names for specific Types. For example, a ResourceCollector could strip the Resource prefix of each class it collects.

Transformers, on the other hand, transform types. They take a `ReflectionClass` and try to transform it to TypeScript.
