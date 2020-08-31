---
title: Customizing the output using annotations
weight: 3
---

When using the `@typescript` annotation, the PHP class's name will be used for the TypeScript type:

```php
/** @typescript */
class Languages extends Enum{
    const en = 'en';
    const nl = 'nl';
    const fr = 'fr';
}
```

The package will produce the following TypeScript:

```tsx
export type Languages = 'en' | 'nl' | 'fr';
```

You can also give the type another name:

```php
/** @typescript Talen **/
class Languages extends Enum{}
```

Now the transformed TypeScript looks like this:

```tsx
export type Talen = 'en' | 'nl' | 'fr';
```

Want to define a specific transformer for the file? You can use the following annotation:

```php
/** 
 * @typescript
 * @typescript-transformer \Spatie\LaravelTypeScriptTransformer\Transformers\SpatieEnumTransformer
 */
class Languages extends Enum{}
```

It is also possible to transform types without adding annotations. You can read more about it [here](https://docs.spatie.be/typescript-transformer/v1/usage/collectors/).
