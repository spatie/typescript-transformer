---
title: Watch mode
weight: 4
---

It is possible to have TypeScript transformer watch your PHP files for changes and automatically update the generated
TypeScript files. You can do this by running:

```bash
php artisan typescript:transform --watch
```

When you're using Laravel you don't need to set up the runner yourself, the Laravel package already has built-in support for watching changes.

For more information about how watch mode works under the hood, see the [watch mode](/docs/typescript-transformer/v3/watch-mode/how-it-works) section.
