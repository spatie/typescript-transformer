---
title: Annotations
weight: 4
---

When using the `@typescript` annotation, the PHP class's name will be used for the Typescript type:

```php
/** @typescript **/
class Languages extends Enum{}
```

You can also give the type another name:

```php
/** @typescript Talen **/
class Languages extends Enum{}
```

Want to define a specific transformer for the file? You can use the following annotation:

```php
/** 
 * @typescript
 * @typescript-transformer \Spatie\LaravelTypescriptTransformer\Transformers\EnumTransformer
 **/
class Languages extends Enum{}
```

It is also possible to transform types without adding annotations, you can read more about it [here](https://docs.spatie.be/typescript-transformer/v1/usage/collectors/)
