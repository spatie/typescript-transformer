---
title: Getting started
weight: 2
---

First we're going to configure the package:

```php
$config = TypeScriptTransformerConfig::create()
    ->searchingPath(__DIR__ . '/src') // path where your php classes are
    ->transformers([MyclabsEnumTransformer::class]) // list of transformers
    ->outputFile(__DIR__ . '/js/generated.d.ts'); // file where Typescript will be written
```

This is the minimal required configuration that should get you started. There are some more configuration methods, but we'll see these later in the documentation.

Let's use this configuration to start the transformation process:

```php
TypescriptTransformer::create($config)->transform();
```

That's it! All the enum classes with a `@typescript` annotation are now converted to typescript.

Classes not converted? You probably should write some transformers. More on that later.
