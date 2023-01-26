---
title: Using transformers 
weight: 4
---

Transformers are the heart of the package. They take a PHP class and try to make a TypeScript definition out of it.

## Default transformers

The package comes with a few transformers out of the box:

- `EnumTransformer`: this transforms a PHP 8.1 native enum
- `MyclabsEnumTransformer`: this transforms an enum from the [`myclabs\enum`](https://github.com/myclab/enum) package
- `SpatieEnumTransformer`: this transforms an enum from the [`spatie\enum`](https://github.com/spatie/enum) package
- `DtoTransformer`: a powerful transformer that transforms entire classes and their properties, you can read more about
  it [here](/docs/typescript-transformer/v2/dtos/typing-properties)
- `InterfaceTransformer`: this transforms a PHP interface and its functions to a Typescript interface. If used, this 
  transformer should always be included before the `DtoTransformer`.

[The laravel package](/docs/typescript-transformer/v2/laravel/installation-and-setup) has some extra transformers:

- `SpatieStateTransformer`: this transforms a state from
  the [`spatie\laravel-model-states`](https://github.com/spatie/laravel-model-status) package
- `DtoTransformer`: a more Laravel specific transformer based upon the default `DtoTransformer`

There are also some packages with community transformers:

- A [transformer](https://github.com/wt-health/laravel-enum-transformer) for `bensampo/laravel-enum` enums

If you've written a transformer package, let us know, and we add it to the list!

You should supply a list of transformers the package should use in your config. The order of transformers matters and can lead to unexpected results if in the wrong order. A PHP declaration (e.g. classes, enums) will go through each transformer and stop once a transformer is able to handle it; this is a problem if `DtoTransformer` is listed before an enum transformer since `DtoTransformer` will incorrectly handle an enum as a class and never allow `MyclabsEnumTransformer` to handle it.

```php
$config = TypeScriptTransformerConfig::create()
    ->transformers([MyclabsEnumTransformer::class, DtoTransformer::class])
   ...
```

### Transforming enums

The package ships with three enum transformers out of the box, by default these enums are transformed to TypeScript types like this:

```tsx
type Language = 'JS' | 'PHP'; 
```

It is possible to transform them to native TypeScript enums by changing the config:

```php
$config = TypeScriptTransformerConfig::create()
    ->transformToNativeEnums()
   ...
```

A transformed enum now looks like this:

```tsx
enum Language {'JS' = 'JS', 'PHP' = 'PHP'};
```

## Writing your own transformers

We've added a whole section in the docs about [writing transformers](../transformers/getting-started).
