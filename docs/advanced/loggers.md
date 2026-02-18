---
title: Loggers
weight: 3
---

The package provides some loggers that can be used to log messages during the transformation process, out of the box the
package provides the following loggers:

- `ArrayLogger`: Logs messages to an array which can be retrieved later
- `SymfonyConsoleLogger`: Logs messages to the Symfony Console output
- `NullLogger`: A logger that logs nothing
- `RayLogger`: Logs messages to Ray (https://spatie.be/docs/ray)
- `MultiLogger`: A logger that logs messages to multiple loggers

The Laravel package provides an additional logger:

- `LaravelConsoleLogger`: Logs messages to the Laravel console output

A logger can be configured when constructing the `Runner`.

Implementing your own logger is possible by implementing the `Logger` interface:

```php
namespace Spatie\TypeScriptTransformer\Support\Loggers;

interface Logger
{
    public function debug(mixed $item, ?string $title = null): void;

    public function info(mixed $item, ?string $title = null): void;

    public function warning(mixed $item, ?string $title = null): void;

    public function error(mixed $item, ?string $title = null): void;
}
```
