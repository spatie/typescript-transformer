---
title: Collectors
weight: 4
---

In some cases, you want to transform classes without annotations. For example, in one of our projects, we've created resource classes that were sent to the frontend using Dto's. We knew these Resources would always have to be converted to Typescript, so writing the `@typescript` annotation was cumbersome.

Collectors allow you to transform classes by a specified transformer. You're already using a collector at this moment. The `@typescript` annotated classes are collected by the package `AnnotationsCollector` collector.

A collector is a class that extends the `Collector` class, and you will have to implement two methods:

```php
class EnumCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        // Can this collector collect this type?
    }

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence
    {
        // Get the `ClassOccurrence` with a Transformer and name for the type
    }
}
```

First, you have to check if the class can be collected by this collector in the `shouldCollect` method. When you can collect the class, `getClassOccurence` should return a correct `ClassOccurence`. A `ClassOccurence` exists of a transformer for the class, and a name the typescript type will have.

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

Collectors are, checked in the order they're defined in the configuration. The package will add the `AnnotationsCollector`, which collects `@typescript` annotated classes automatically at the end.
