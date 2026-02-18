---
title: Managing transformers
weight: 2
---

Besides the `transformer()` method which appends transformers to the end of the list, the configuration provides two additional methods for more control:

**Prepending transformers** — adds transformers to the beginning of the list, useful when your transformer should take priority over others:

```php
$config->prependTransformer(new HighPriorityTransformer());
```

**Replacing transformers** — swaps an existing transformer for a different one, useful within extensions to override a default transformer:

```php
$config->replaceTransformer(
    AttributedClassTransformer::class,
    new MyCustomClassTransformer()
);
```
