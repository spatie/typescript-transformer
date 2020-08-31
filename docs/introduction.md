---
title: Introduction
weight: 1
---

This package allows you to convert PHP types to TypeScript.

Let's say you have an enum:

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

Wouldn't it be cool if you could have an automatically generated TypeScript definitions like this?

```tsx
export type Languages = 'typescript' | 'php';
```

This package will automatically generate such definitions for you, the only thing you have to do is adding this annotation:

```php
/** @typescript */
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

You can even take it a step further and generate TypeScript from public class properties:

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

```ts
export type User = {
    int: number;
    name: string;
    address: string | null;
}
```
