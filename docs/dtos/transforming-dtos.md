---
title: Customization
weight: 2
---

The package provides a `DtoTransformer` out of the box. This transformer will convert all public non-static properties of a class to TypeScript types.

For the Laravel, a special `DtoTransformer` was written with some extra Laravel niceties. You can find this transformer in the [spatie/laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer) package.

This transformer was built to be extended for specific use cases:

**canTransform**

returns a boolean whether the transformer can transform the class

**transformProperties**

Takes a collection of reflection properties and transforms them into TypeScript

**transformMethods**

Takes a collection of methods and transforms them into TypeScript (disabled by default)

**transformExtra**

Allows you to add extra definitions to the current type

**typeProcessors**

Initiates the type processors that will run in the transformer

**resolveProperties**

Collects the properties that will be transformed
