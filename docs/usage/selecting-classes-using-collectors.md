---
title: Selecting classes using collectors
weight: 5
---

In some cases, you'll want to transform classes without the `@typescript` annotation. For example Laravel's API resources are almost always sent to the front and should always have a TypeScript defintion ready to be used.

Collectors allow you to specify what PHP classes should be transformed to TypeScript and what transformer should be used. 
The package comes out of the box with the pre-configured `DefaultCollector` to find and transform classes marked with the `@typescript` annotation and attributes.

A collector is any class that extends the `Collector` class  and implements the `getTransformedType` method:

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

Collectors will be checked in order, if a perfect collector fit was found for a type. Then all the other collectors in line will be ignored for that type. So always add your own collectors before the `DefaultCollector` since this one will probably will consume all the types within your application.

## Difference between Collectors and Transformers

Allthough the two concepts share a very similar interface they are indeed different.

A collector takes Types and gives them to a specific transformer that was decided by the collector, for example, the `DefaultCollector` will run a type through each transformer you've configured. Collectors can also change names for specific Types, for example, a ResourceCollector could strip the Resource prefix of each Type it transforms.

Transformers on the other hand transform types, they take a `ReflectionClass` and try to transform it to TypeScript.
