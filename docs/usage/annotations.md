---
title: Annotations
weight: 3
---

When using the `@typescript` annotation, the PHP class's name will be used for the Typescript type:

```php
/** @typescript **/
class Languages extends Enum{
    const en = 'en';
    const nl = 'nl';
    const fr = 'fr';
}
```

This will produce the following Typescript:

```typescript
export type Languages = 'en' | 'nl' | 'fr';
```

You can also give the type another name:

```php
/** @typescript Talen **/
class Languages extends Enum{}
```

Now the transformed Typescript looks like this:

```typescript
export type Talen = 'en' | 'nl' | 'fr';
```

Want to define a specific transformer for the file? You can use the following annotation:

```php
/** 
 * @typescript
 * @typescript-transformer \Spatie\LaravelTypescriptTransformer\Transformers\SpatieEnumTransformer
 **/
class Languages extends Enum{}
```

It is also possible to transform types without adding annotations, you can read more about it [here](https://docs.spatie.be/typescript-transformer/v1/usage/collectors/)
