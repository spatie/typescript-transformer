---
title: Logging in providers
weight: 3
---

TypeScript transformer provides a logging mechanism that can be used within providers to log messages during the
transformation process. This is particularly useful for debugging and tracking the transformation flow and displaying
errors or other important
information.

When implementing the `LoggingTransformedProvider` interface, the `setLogger` method receives a `Logger` instance as an
additional parameter:

```php
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;

class CustomTransformedProvider implements LoggingTransformedProvider, TransformedProvider
{
    protected Logger $logger;

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function provide(
        TypeScriptTransformerConfig $config,
    ): array {
        $this->logger->info('Starting transformation process in CustomTransformedProvider.');

        // Some of

        $this->logger->info('Finished transformation process in CustomTransformedProvider.');
    }
}
```

A log always exists of an item which can be any type which is JSON encodable and an optional title for context:

```php
$this->logger->debug($transfomed->reference, 'Transformed reference details');
$this->logger->info($transfomed->typeScriptNode, 'TypeScript node details');
$this->logger->warning($transfomed->getName(), 'Potential issue with transformed item');
$this->logger->error($transfomed->changed, 'Error encountered during transformation');
```
