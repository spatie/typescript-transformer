---
title: Installation and setup
weight: 1
---

## Basic installation

You can install this package via composer:

```bash
composer require spatie/laravel-typescript-transformer
```

The package will automatically register a service provider.

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerServiceProvider"
```

This is the default content of the config file:

```php
return [
    /*
     * The paths where typescript-transformer will look for PHP classes
     * to transform, this will be the `app` path by default.
     */

    'searching_paths' => [
        app_path()
    ],

    /*
     * Collectors will search for classes in your `searching_path` and choose the correct
     * transformer to transform them. By default, we include an AnnotationCollector
     * which will search for @typescript annotated classes to transform.
     */

    'collectors' => [
        Spatie\TypeScriptTransformer\Collectors\AnnotationCollector::class,
    ],

    /*
     * Transformers get PHP classes(e.g., enums) as an input and will output
     * a TypeScript representation of the PHP class.
     */

    'transformers' => [
        Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer::class,
        Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer::class,
        Spatie\TypeScriptTransformer\Transformers\DtoTransformer::class,
    ],

    /*
     * In your classes you sometimes have types that should always be replaced
     * by the same TypeScript representations. For example, you can replace
     * a Datetime always with a string. You define this replacements here.
     */

    'default_type_replacements' => [
        DateTime::class => 'string',
        DateTimeImmutable::class => 'string',
        Carbon::class => 'string',
        CarbonImmutable::class => 'string',
    ],

    /*
     * The package will write the generated TypeScript to this file.
     */

    'output_file' => resource_path('types/generated.d.ts'),

    /*
     * When enabled, the generated TypeScript file will be formatted by
     * prettier when all types are transformed.
     */

    'enable_formatting' => false,
];
```
