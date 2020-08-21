---
title: Transformers
weight: 2
---

Transformers are the heart of the package. They take a PHP class and determine if it can be transformed into Typescript, if that's possible, the transformer will transform the PHP class to Typescript.

## Default transformers

Although writing your own transformers isn't that difficult we've added a few transformers to get started:

- `MyclabsEnumTransformer`: this converts an enum from the `myclabs\enum` package
- `DtoTransformer`: a powerful transformer that transforms classes and their properties, you can read more about it [here](https://docs.spatie.be/typescript-transformer/v1/dtos/transforming/)

The laravel package has some extra transformers:

- `SpatieEnumTransformer`: this converts an enum from the `spatie\enum` package
- `SpatieStateTransformer`: this converts a state from the `spatie\laravel-model-states` package

## Writing transformers

A transformer is a class which implements the `Transformer` interface:

```php
use Spatie\TypescriptTransformer\Transformers\Transformer;

class EnumTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        // can this transformer handle the given class?
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        // get the typescript representation of the class
    }
}
```

In the `canTransform` method, you should decide if this transformer can convert the class. In the `transform` method, you should return a transformed version of the PHP type as a `TransformedType`. Let's take a look at how to create one.

### Creating transformed types

A `TransformedType` always has three properties: the `ReflectionClass` of the type you're transforming, the name of the type and off course the transformed Typescript code:

```php
TransformedType::create(
    ReflectionClass $class, // The reflection class
    string $name, // The name of the Type
    string $transformed // The Typescript representation of the class
);
```

What about creating types that depend on other types? It is possible to add a fourth argument to the `create` method:

```php
TransformedType::create(
    ReflectionClass $class,
    string $name,
    string $transformed,
    MissingSymbolsCollection $missingSymbols
);
```

A `MissingSymbolsCollection` will contain links to other types. The package will replace these links with correct Typescript types. So, for example, say you have this class:

```php
/** @typescript **/
class User extends DataTransferObject
{
    public string $name;
    
    public RoleEnum $role;
}
```

As you can see it has a RoleEnum as a property, which looks like this:

```php
/** @typescript **/
class RoleEnum extends Enum
{
    const GUEST = 'guest';
    
    const ADMIN = 'admin';
}
```

When transforming this class we don't know what the `RoleEnum` will be, but since it is also converted to Typescript the package will produce the following output:

```typescript
export type RoleEnum = 'guest' | 'admin';

export type User = {
    name : string;
    role : RoleEnum;
}
```

In your transformers you should add such linked properties to the `MissingSymbolsCollection` as such:

```php
$type = $missingSymbols->add(RoleEnum::class); // Will return {%RoleEnum::class%}
```

The `add` method will return a token that can be used in your transformed type, to be replaced later. It's the link we described above between the types.

When no type was found(for example: because it wasn't converted to Typescript), then the type will be replaced with the `any` Typescript type.

#### Inline types

It is also possible to create an inline type. These types will not create a whole new Typescript type but replace a type inline in another type. In our previous example, if we would transform `Enum` classes with an inline type, the generated Typescript would look like this:

```typescript
export type User = {
    name : string;
    role : 'guest' | 'admin';
}
```

Inline types can be created like the regular types, but they do not need a name:

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
