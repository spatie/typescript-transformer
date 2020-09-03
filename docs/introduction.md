---
title: Introduction
weight: 1
---

This package allows you to convert PHP classes to TypeScript. 


This class...

```php
/** @typescript */
class User
{
    public int $id;
    public string $name;
    public ?string $address;
}
```

... will be converted to this TypeScript type:

```ts
export type User = {
    id: number;
    name: string;
    address: string | null;
}
```

Here's another example.

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

The `Languages` enum will be converted to:

```tsx
export type Languages = 'typescript' | 'php';
```
