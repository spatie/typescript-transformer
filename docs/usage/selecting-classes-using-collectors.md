---
title: Selecting classes using collectors
weight: 4
---

In some cases, you'll want to transform classes without the `@typescript` annotation. For example Laravel's API resources are almost always sent to the front and should always have a TypeScript defintion ready to be used.

Collectors allow you to specify what PHP classes should be transformed to TypeScript and what transformer should be used. 
The package comes out of the box with the pre-configured `AnnotationsCollector` to find and transform classes marked with the `@typescript` annotation.

A collector is any class that extends the `Collector` class  and implements the `shouldCollection` and `getClassOccurrence` methods:

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

First, you have to check if the class can be collected by this collector in the `shouldCollect` method. When you can collect the class, `getClassOccurence` should return a correct `ClassOccurence`. The `ClassOccurence` contains the type's name and what transformer to use for the class.

You can easily create a `ClassOccurrence` as such:

```php
ClassOccurrence::create(
    new EnumTransformer(),
    'MyAwesomeType'
);
```

Don't forget to add the collector to your configuration:

```php
$config = TypeScriptTransformerConfig::create()
    ->collectors([EnumCollector::class])
   ...
```

Collectors are checked in the same order they're defined in the configuration. The package will add the `AnnotationsCollector`, which collects `@typescript` annotated classes automatically at the end. This always you to overwrite this behaviour by adding your own version of the `AnnotationsCollector`.
