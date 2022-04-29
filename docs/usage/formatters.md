---
title: Formatters
weight: 7
---

This output file with all the transformed types can be formatted using tools like Prettier. We ship an ESLint and a Prettier formatter, which will run after the output file is generated. For instance, you can configure the Prettier formatter as such:

```php
$config = TypeScriptTransformerConfig::create()
    ->formatter(PrettierFormatter::class)
    ...
```

You could also implement your own formatter by implementing the `Formatter` interface:

```php
interface Formatter
{
    public function format(string $file): void;
}
```

Within the `format` method, a path to the output file is given, which should be formatted.
