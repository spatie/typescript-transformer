---
title: Executing the transform command
weight: 2
---

After configuring the package in the `typescript-transformer` config file, you can run this command to write the typescript output file:

```bash
php artisan typescript:transform
```

## Command options

There are some extra commands you can use when running the command. It is also possible to transform classes in a specified path:

```bash
php artisan typescript:transform --path=app/Enums
```

Or you can define another output file than the default one:

```bash
php artisan typescript:transform --output=types.d.ts
```

This file will be stored in the resource's path of your Laravel application.

It is also possible to automatically format the generated TypeScript with Prettier, ESLint, or a custom formatter:

```bash
php artisan typescript:transform --format
```
