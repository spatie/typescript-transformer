---
title: Using transformers 
weight: 4
---

Transformers are the heart of the package. They take a PHP class and try to make a TypeScript definition out of it.

## Default transformers

The package comes with a few transformers out of the box:

- `MyclabsEnumTransformer`: this transforms an enum from the [`myclabs\enum`](https://github.com/myclab/enum) package
- `SpatieEnumTransformer`: this transforms an enum from the [`spatie\enum`](https://github.com/spatie/enum) package
- `DtoTransformer`: a powerful transformer that transforms entire classes and their properties, you can read more about
  it [here](https://docs.spatie.be/typescript-transformer/v2/dtos/transforming/)

[The laravel package](/docs/typescript-transformer/v2/laravel/installation-and-setup) has some extra transformers:

- `SpatieStateTransformer`: this transforms a state from
  the [`spatie\laravel-model-states`](https://github.com/spatie/laravel-model-status) package
- `DtoTransformer`: a more Laravel specific transformer based upon the default `DtoTransformer`

There are also some packages with community transformers:

- A [transformer](https://github.com/wt-health/laravel-enum-transformer) for `bensampo/laravel-enum` enums

If you've written a transformer package, let us know, and we add it to the list!

## Writing transformers

