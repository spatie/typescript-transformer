---
title: Replacing common types
weight: 5
---

Some PHP classes should be transformed into something atypical, an example of this is the `DateTime` class. When you
send such an object to the front it will be represented by a string rather than an object. TypeScript transformer allows
you to replace these kinds types with an appropriate TypeScript type.

Replacing types can be done in the config:

```php
$config->replaceType(DateTime::class, 'string');
```

Now all `DateTime` objects will be transformed to a string in TypeScript. This also includes inherited classes
like `Carbon`, those will also be transformed to a string.

When using an interface like `DateTimeInterface` you can also replace it with a TypeScript type:

```php
$config->replaceType(DateTimeInterface::class, 'string');
```

All classes that implement `DateTimeInterface` will be transformed to a string in TypeScript.

## Replacements

As we've seen before it is possible to replace types by writing them out like you would do in an annotation, this allows
you to build complex types, for example:

```php
$config->replaceType(DateTimeInterface::class, 'array{day: int, month: int, year: int}');
```

From now on, all `DateTimeInterface` objects will be replaced by the following TypeScript object:

```ts
{
    day: number;
    month: number;
    year: number;
}
```

It is also possible to define a replacement as an internal TypeScript node(more on that later):

```php
$config->replaceType(DateTimeInterface::class, new TypeScriptString());
```

Or use a closure to define the replacement:

```php
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;

$config->replaceType(DateTimeInterface::class, function (TypeScriptReference $reference) {
    return new TypeScriptString();
});
```
