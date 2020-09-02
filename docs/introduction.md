---
title: Introduction
weight: 1
---

This package allows you to convert PHP types to TypeScript. 

This enum ...

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

... can be converted to this TypeScript definition:

```tsx
export type Languages = 'typescript' | 'php';
```


It can also convert a class like this to TypeScript.

```php
/** @typescript */
class User
{
    public int $id;

    public string $name;

    public ?string $address;
}
```

This is the TypeScript equivalent.

```ts
export type User = {
    int: number;
    name: string;
    address: string | null;
}
```
