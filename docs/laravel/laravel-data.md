---
title: Laravel Data
weight: 2
---

If you're using [Laravel Data](https://spatie.be/docs/laravel-data), this package provides a dedicated extension that
automatically transforms your Data classes to TypeScript types with full support for lazy properties, hidden fields and
property name mapping.

## Setup

Add the `LaravelDataTypeScriptTransformerExtension` to your `TypeScriptTransformerServiceProvider`:

```php
use Spatie\LaravelTypeScriptTransformer\LaravelData\LaravelDataTypeScriptTransformerExtension;

protected function configure(TypeScriptTransformerConfigFactory $config): void
{
    $config->extension(new LaravelDataTypeScriptTransformerExtension());
}
```

That's it. Every Data class in your configured transform directories will now be picked up and converted to TypeScript.

## How it works

Given a Data class like this:

```php
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age,
        public ?string $avatar,
    ) {
    }
}
```

TypeScript Transformer will generate:

```ts
export type UserData = {
    name: string;
    email: string;
    age: number;
    avatar: string | null;
};
```

The transformer automatically picks up all public properties and resolves their types, including complex types defined
via docblocks.

## Lazy properties

Laravel Data's lazy properties allow you to conditionally include data in responses. When TypeScript Transformer
encounters a lazy type, it removes the lazy wrapper and makes the property optional instead:

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class UserData extends Data
{
    public function __construct(
        public string $name,
        public Lazy|string $email,
        public Lazy|int $age,
    ) {
    }
}
```

Generates:

```ts
export type UserData = {
    name: string;
    email?: string;
    age?: number;
};
```

This works for all lazy types: `Lazy`, `ClosureLazy`, `ConditionalLazy`, `DefaultLazy`, `InertiaDeferred`,
`InertiaLazy`, `LivewireLostLazy` and `RelationalLazy`.

The `Optional` type is handled the same way, making the property optional in TypeScript.

### Custom lazy types

If you have custom lazy types, you can register them:

```php
$config->extension(new LaravelDataTypeScriptTransformerExtension(
    customLazyTypes: [
        MyCustomLazy::class,
    ],
));
```

## Hidden properties

Properties marked with the `#[Hidden]` attribute (from either the TypeScript Transformer or Laravel Data package) will
be excluded from the generated TypeScript type:

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Hidden;

class UserData extends Data
{
    public function __construct(
        public string $name,
        #[Hidden]
        public string $password,
    ) {
    }
}
```

Generates:

```ts
export type UserData = {
    name: string;
};
```

## Property name mapping

Laravel Data's `#[MapOutputName]` and `#[MapName]` attributes are respected. When a property has a mapped output name,
the TypeScript property will use that name:

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapOutputName;

class UserData extends Data
{
    public function __construct(
        #[MapOutputName('full_name')]
        public string $name,
        public string $email,
    ) {
    }
}
```

Generates:

```ts
export type UserData = {
    full_name: string;
    email: string;
};
```

## Custom data collections

By default, the transformer knows how to handle `Collection`, `EloquentCollection` and `DataCollection` as array-like
structures. If you have custom collection classes, you can register them:

```php
$config->extension(new LaravelDataTypeScriptTransformerExtension(
    customDataCollections: [
        MyCustomDataCollection::class,
    ],
));
```
