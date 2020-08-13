---
title: Collectors
weight: 5
---

In some cases, you want to transform classes without annotation. For example, in one of our projects, we've created resource classes which were sent to the front using DTO's. We knew these Resources would always have to be converted to Typescript, so writing the `@typescript` annotation was a bit cumbersome.

Collectors allow you to transform classes by a specified transformer. Actually, you're already using a Collector, the `@typescript` annotated classes are collected by the package `AnnotationsCollector`.

A collector is a class that extends the `Collector` class, and you will have to implement two methods:

```php
class EnumCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        // Can this type be collected by this collector?
    }

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence
    {
        // Get the `ClassOccurrence` with a Transformer and name for the type
    }
}
```

First, you check if the class can be collected by this collector in `shouldCollect` when you can collect the class, `getClassOccurence` should return a correct `ClassOccurence`. A `ClassOccurence` exists of a transformer for the class, and a name the typescript type will have.

You can easily create a `ClassOccurrence` as such:

```php
ClassOccurrence::create(
    new EnumTransformer(),
    'MyAwesomeType'
);
```

In the end you have to add the collector to your configuration:

```php
$config = TypeScriptTransformerConfig::create()
    ->collectors([EnumCollector::class])
	...
```

Collectors are checked in the order they're defined in the config, the package adds the `AnnotationsCollector` which collects `@typescript` annotated classes automatically at the end.
