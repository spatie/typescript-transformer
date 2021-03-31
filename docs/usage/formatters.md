---
title: Formatting TypeScript
weight: 7
---

This output file with all the transformed types can be formatted using tools like Prettier, you can automatically run prettier when the output file is generated as such:

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

Within the `format` method a path to the output file is given which can be formatted.
