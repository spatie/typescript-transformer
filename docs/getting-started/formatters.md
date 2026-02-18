---
title: Formatters
weight: 6
---

The package tries to format the transformed TypeScript as good as possible, but sometimes this could be far from
perfect. That's why it is possible to automatically format the TypeScript code after transforming.

By default, the package has support for two formatters:

- `PrettierFormatter`: Formats the TypeScript code using Prettier
- `EslintFormatter`: Formats the TypeScript code using ESLint

You can add a formatter to the configuration like this:

```php
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;

$config->formatter(new PrettierFormatter());
```

It is possible to create your own formatter by implementing the `Formatter` interface:

```php
interface Formatter
{
    public function format(array $files): void;
}
```

The `$files` array contains the TypeScript files that need to be formatted, you can format them in any way you like.
