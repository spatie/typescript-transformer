---
title: Writers
weight: 6
---

When all types are transformed, the package will write them out in one big output file. A writer will determine how this output will look.

You can configure a writer as such:

```php
$config = TypeScriptTransformerConfig::create()
    ->writer(TypeDefinitionWriter::class)
    ...
```

By default, the `TypeDefinitionWriter` is used when no writer was configured. You can use one of the Writers shipped with the package out of the box or write your own one.

## TypeDefinitionWriter

The `TypeDefinitionWriter` will group types into namespaces that follow the structure of the PHP namespaces.

Let's take a look at an example with two PHP classes:

```php
namespace App\Enums;

#[TypeScript]
class Language extends Enum
{
    public const nl = 'nl';
    public const en = 'en';
    public const fr = 'fr';
}
```

and

```php
namespace App\Models;

#[TypeScript]
class User
{
    public string $name;
    public \App\Enums\Language $language;
}
```

The written TypeScript will look like this:

```tsx
namespace App.Enums {
    export type Language = 'nl' | 'en' | 'fr';
};

namespace App.Models {
    export type User = {
        name: string;
        language: App.Enums.Language
    };
};
```

## ModuleWriter

The `ModuleWriter` will ignore namespaces and list all the types as individual modules.

When we use the same classes from the previous example, the written TypeScript now looks like this:

```tsx
export type Language = 'nl' | 'en' | 'fr';
export type User = {
    name: string;
    language: Language
};
```

## Building your own writer

A writer is a class implementing the `Writer` interface:

```php
interface Writer
{
    public function format(TypesCollection $collection): string;

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool;
}
```

The `format` method takes a `TypesCollection` and outputs a string containing the TypeScript representation.

In the `replacesSymbolsWithFullyQualifiedIdentifiers` method, a boolean is returned that indicates whether to use fully qualified identifiers when replacing symbols or not.

The `TypeDefinitionWriter` uses fully qualified identifiers with a namespace, whereas the `ModuleWriter` doesn't.

