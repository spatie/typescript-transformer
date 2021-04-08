---
title: Getting started
weight: 2
---

## Framework agnostic

```php
$config = TypeScriptTransformerConfig::create()
    // path where your PHP classes are
    ->autoDiscoverTypes(__DIR__ . '/src')
    // list of transformers
    ->transformers([MyclabsEnumTransformer::class]) 
    // file where TypeScript type definitions will be written
    ->outputFile(__DIR__ . '/js/generated.d.ts'); 
```

This is the minimal required configuration that should get you started. There are some more configuration options, but we'll go over these later in the documentation.

Let's use this configuration to start the transformation process:

```php
TypeScriptTransformer::create($config)->transform();
```

That's it! Each class with a `@typescript` annotation or `#[TypeScript]` are now transformed to TypeScript if a suitable transformer can be found.

## Laravel

Are you using Laravel? Then you can use a Laravel config file, more info about that [here](https://docs.spatie.be/typescript-transformer/v2/laravel/installation-and-setup/).
