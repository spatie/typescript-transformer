---
title: Helpers
weight: 5
---

When building custom transformed providers you may need access to actions like transpiling PHP types to TypeScript, discovering certain types and more.

```php
use Spatie\TypeScriptTransformer\TransformedProviders\ActionAwareTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProviderActions;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;

class CustomProvider implements TransformedProvider, ActionAwareTransformedProvider
{
    private TransformedProviderActions $actions;

    public function setActions(TransformedProviderActions $actions): void
    {
        $this->actions = $actions;
    }

    public function provide(): array
    {
        $classNode = $this->actions->parseUserDefinedTypeAction->execute('Record<string, int>');
    }
}
```

The `TransformedProviderActions` object provides:

- `loadPhpClassNodeAction` - Load a `PhpClassNode` from a file path
- `discoverTypesAction` - Discover PHP classes in directories
- `transpilePhpStanTypeToTypeScriptNodeAction` - Transpile PHPStan doc types to TypeScript nodes
- `transpilePhpTypeNodeToTypeScriptNodeAction` - Transpile native PHP types to TypeScript nodes
- `parseUserDefinedTypeAction` - Parse a user-defined type string into a TypeScript node
