---
title: Introduction
weight: 1
---
**This package is still under heavy development, please do not use it (yet)**

Always wanted type safety within PHP and Typescript without duplicating a lot of code? Then you will like this package! Let's say you have an enum:

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

Wouldn't it be cool if you could have an automatically generated Typescript definition like this:

```typescript
export type Languages = 'typescript' | 'php';
```

This package will automatically generate such definitions for you, the only thing you have to do is adding this annotation:

```php
/** @typescript **/
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

You can even take it a bit further and generate Typescript from classes:

```php
/** @typescript */
class User
{
    public int $id;

    public string $name;

    public ?string $address;
}
```

This will be transformed to:

```typescript
export type User = {
    int: number;
    name: string;
    address: string | null;
}
```
