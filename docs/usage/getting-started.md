---
title: Getting started
weight: 1
---

First we're going to configure the package:

```php
$config = TypeScriptTransformerConfig::create()
    ->searchingPath(__DIR__ . '/src') // path where your PHP classes are
    ->transformers([MyclabsEnumTransformer::class]) // list of transformers
    ->outputFile(__DIR__ . '/js/generated.d.ts'); // file where TypeScript type definition will be written
```

This is the minimal required configuration that should get you started. There are some more configuration options, but we'll go over these later in the documentation.

Let's use this configuration to start the transformation process:

```php
TypescriptTransformer::create($config)->transform();
```

That's it! All the enum classes with a `@typescript` annotation are now transformed to Typescript.

Classes not converted? You probably should write some transformers. Let's continue!
