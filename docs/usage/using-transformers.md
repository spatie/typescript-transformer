---
title: Using transformers
weight: 2
---

Transformers are the heart of the package. They take a PHP class and determine if it can be transformed into TypeScript, if that's possible, the transformer will transform the PHP class to a TypeScript definition.

## Default transformers

The package comes with a few transformers out of the box:

- `MyclabsEnumTransformer`: this transforms an enum from [the `myclabs\enum` package](https://github.com/myclab/enum)
- `DtoTransformer`: a powerful transformer that transforms classes and their properties, you can read more about it [here](https://docs.spatie.be/typescript-transformer/v1/dtos/transforming/)

[The laravel package](docs/typescript-transformer/v1/laravel/installation-and-setup) has some extra transformers:

- `SpatieEnumTransformer`: this transforms an enum from [the `spatie\enum` package](https://github.com/spatie/enum)
- `SpatieStateTransformer`: this transforms a state from [the `spatie\laravel-model-states` package](https://github.com/spatie/laravel-model-status)

## Writing transformers

A transformer is a class that implements the `Transformer` interface:

```php
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class EnumTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        // can this transformer handle the given class?
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        // get the TypeScript representation of the class
    }
}
```

In the `canTransform` method, you should decide if this transformer can convert the class. In the `transform` method, you should return a transformed version of the PHP type as a `TransformedType`. Let's take a look at how to create one.

### Creating transformed types

A `TransformedType` always has three properties: the `ReflectionClass` of the type you're transforming, the name of the type and off course the transformed TypeScript code:

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

A `MissingSymbolsCollection` will contain links to other types. The package will replace these links with correct TypeScript types. 

Consider the following class as an example:

```php
/** @typescript **/
class User extends DataTransferObject
{
    public string $name;
    
    public RoleEnum $role;
}
```

As you can see it has a `RoleEnum` as a property, which looks like this:

```php
/** @typescript **/
class RoleEnum extends Enum
{
    const GUEST = 'guest';
    
    const ADMIN = 'admin';
}
```

When transforming the `User` class we don't have any context or types for the `RoleEnum`. The transformer can register this missing symbols for a property using the `MissingSymbolsCollection` as such: 

```php
$type = $missingSymbols->add(RoleEnum::class); // Will return {%RoleEnum::class%}
```

The `add` method will return a token that can be used in your transformed type. It's the link we described above between the two types and will later be replaced by the actual type implementation.

When no type was found (for example: because it wasn't converted to TypeScript) the type will default to TypeScript's `any` type.

But in this specific example, the package will produce the following output:

```tsx
export type RoleEnum = 'guest' | 'admin';

export type User = {
    name : string;
    role : RoleEnum;
}
```

#### Inline types

It is also possible to create an inline type. These types will not create a whole new TypeScript type but replace a type inline in another type. In our previous example, if we would transform `Enum` classes with an inline type, the generated TypeScript would look like this:

```ts
export type User = {
    name : string;
    role : 'guest' | 'admin';
}
```

Inline types can be created like regular `TransformedType`s but they do not need a name:

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

When you create a new transformer, do not forget to add it to the list of transformers in your configuration!
