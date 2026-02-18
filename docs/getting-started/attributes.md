---
title: Special attributes
weight: 3
---

Classes can have attributes that change the way they are transformed, let's go through them.

Using the `#[TypeScript]` attribute is not only a way to tell typescript-transformer to transform a class, but it can
also be used to change the name of the transformed class:

```php
#[TypeScript(name: 'UserWithoutEmail')]
class User
{
    public int $id;
    public string $name;
}
```

This will transform the `User` class to `UserWithoutEmail` in TypeScript.

```ts
export type UserWithoutEmail = {
    id: number;
    name: string;
}
```

Each type will be located somewhere either being a file when using the `ModuleWriter` or in a single file when using
the `GlobalNamespaceWriter`. The location of the type can be changed by using the `#[TypeScript]` attribute:

```php
#[TypeScript(location: ['Data', 'Users'])]
class User
{
    public int $id;
    public string $name;
}
```

This will transform as such:

```ts
declare namespace Data.Users {
    export type User = {
        id: number;
        name: string;
    };
}
```

By default, the location is based on the namespace of the PHP class.

It is possible to completely remove a class from the TypeScript output by using the `#[Hidden]` attribute:

```php
#[Hidden]
enum Members: string
{
    case John = 'john';
    case Paul = 'paul';
    case George = 'george';
    case Ringo = 'ringo';
}
```

This is particularly useful when using the `EnumTransformer` and you want to hide certain enums from the TypeScript.
