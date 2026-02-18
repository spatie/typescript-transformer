---
title: Extensions
weight: 1
---

Extensions allow packages to enrich the TypeScript transformer configuration. An extension implements the `TypeScriptTransformerExtension` interface:

```php
use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class MyExtension implements TypeScriptTransformerExtension
{
    public function enrich(TypeScriptTransformerConfigFactory $factory): void
    {
        $factory->transformer(new MyCustomTransformer());
        $factory->provider(new MyCustomProvider());
    }
}
```

Register an extension in the configuration:

```php
$config->extension(new MyExtension());
```
