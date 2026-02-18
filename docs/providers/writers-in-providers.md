---
title: Using different writers in providers
weight: 2
---

Currently, TypeScript transformer will output all transformed objects using the default writer configured. While most of
the time this is sufficient, there are cases where you might want to have more control over how certain transformed
objects are written down.

For example, in the Laravel package all types from classes, enums, and interfaces are written to a single file using
namespaces. Yet the package also provide some helper functions allowing you to generate URLs for your routes. These
helper functions are best written to a separate file without namespaces.

In order to achieve this, TypeScript transformer allows you to use different writers per transformed object, you can
set a writer on a transformed object as such:

```php
$transformed->setWriter(new ModuleWriter());
```

Please notice that you can only set writers on a transformed object once and we advise you for performance reasons
to create a writer in the constructor of your provider and reuse it for all transformed objects within that provider.

It is still possible to reference transformed objects written down by writers from other providers or the default
writer, TypeScript transformer will take care of linking them together and generating the correct import statements.
