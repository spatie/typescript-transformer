---
title: Getting started
weight: 1
---

A transformer is a class that implements the `Transformer` interface:

```php
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class EnumTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
    }
}
```

In the `transform` method, you should transform a PHP `ReflectionClass` into a `TransformedType`. This `TransformedType` will
include the TypeScript representation of the PHP class and some extra information.

When a transformer cannot transform the given `ReflectionClass` then the method should return `null`, indicating that the transformer is not suitable for the type.

### Creating transformed types

A `TransformedType` always has three properties: the `ReflectionClass` of the type you're transforming, the name of the
type and, of course, the transformed TypeScript code:

```php
TransformedType::create(
    ReflectionClass $class, // The reflection class
    string $name, // The name of the Type
    string $transformed // The TypeScript representation of the class
);
```

For types that depend on other types a fourth argument can be passed to the `create` method:

```php
TransformedType::create(
    ReflectionClass $class,
    string $name,
    string $transformed,
    MissingSymbolsCollection $missingSymbols
);
```

A `MissingSymbolsCollection` will contain references to other types. The package will replace these references with
correct TypeScript types.

Consider the following class as an example:

```php
/** @typescript **/
class User extends DataTransferObject
{
    public string $name;
    
    public RoleEnum $role;
}
```

As you can see, it has a `RoleEnum` as a property, which looks like this:

```php
/** @typescript **/
class RoleEnum extends Enum
{
    const GUEST = 'guest';
    const ADMIN = 'admin';
}
```

When transforming the `User` class we don't have any context or types for the `RoleEnum`. The transformer can register
this missing symbols for a property using the `MissingSymbolsCollection` as such:

```php
$type = $missingSymbols->add(RoleEnum::class); // Will return {%RoleEnum::class%}
```

The `add` method will return a token that can be used in your transformed type. It's a link between the two types and
will later be replaced by the actual type implementation.

When no type was found (for example: because it wasn't converted to TypeScript), the type will default to
TypeScript's `any` type.

In the end, the package will produce the following output:

```tsx
export type RoleEnum = 'guest' | 'admin';

export type User = {
    name: string;
    role: RoleEnum;
}
```

#### Inline types

It is also possible to create an inline type. These types are replaced directly in other types. You can read more about
inline types [here](/docs/typescript-transformer/v2/usage/annotations#inlining-types).

Inline types can be created like a regular `TransformedType` but they do not need a name:

```php
TransformedType::createInline(
    ReflectionClass $class,
    string $transformed
);
```

When required you can also add a `MissingSymbolsCollection`:

```php
TransformedType::createInline(
    ReflectionClass $class,
    string $transformed,
    MissingSymbolsCollection $missingSymbols
);
```

When you create a new transformer, don't forget to add it to the list of transformers in your configuration!
