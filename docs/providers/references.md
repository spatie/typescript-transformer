---
title: Referencing types
weight: 4
---

Types sometimes reference other types like PHP classes referencing other PHP classes. Within the package a concept of
references is used to link these types together.

When creating `Transformed` objects we've always used the `ClassStringReference` since we were referencing PHP classes,
sometimes you might be transforming something which is not a PHP class for example a list of strings. In this case, you
can use a `CustomReference`:

```php
use Spatie\TypeScriptTransformer\References\CustomReference;

new Transformed(
    typeScriptNode: new TypeScriptAlias(
        new TypeScriptIdentifier('Type'),
        new TypeScriptUnion([new TypeScriptLiteral('PHP'), new TypeScriptLiteral('TypeScript')]),
    ),
    reference: new CustomReference('my_languages_package', 'some_languages'),
    location: ['App', 'Languages'],
);
```

A custom reference should be unique for each type, that's why it is built up from a group and a name. We advise you when
creating a package (or if you're implementing a feature within your app) to choose a custom group name in order not to
conflict with other packages.

In the end the transformed TypeScript will look like this:

```ts
namespace App.Languages {
    export type Type = 'PHP' | 'TypeScript';
}
```

It is possible to reference this type in another `Transformed` object:

```php
new Transformed(
    typeScriptNode: new TypeScriptAlias(
        new TypeScriptIdentifier('Compiler'),
        new TypeScriptObject([
            new TypeScriptProperty('type', new CustomTypeReference('my_languages_package', 'some_languages')),
        ]),
    ),
    reference: new ClassStringReference(Compiler::class),
    location: ['App', 'Compilers'],
);
```

The transformed TypeScript now will look like this:

```ts
namespace App.Compilers {
    export type Compiler = {
        type: App.Languages.Type;
    };
}
```

Since we're using the same reference, the package is smart enough to link them together when transforming to TypeScript.

Off course, you can also reference PHP classes in the same way:

```php
new Transformed(
    typeScriptNode: new TypeScriptAlias(
        new TypeScriptIdentifier('Post'),
        new TypeScriptObject([
            new TypeScriptProperty('publisher', new TypeScriptReference(new ClassStringReference(User::class))),
        ]),
    ),
    reference: new ClassStringReference(User::class),
    location: ['App', 'Models'],
);
```
