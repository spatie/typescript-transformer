---
title: Introduction
weight: 1
---

Internally the package uses TypeScript nodes to represent TypeScript types, these nodes can be used to build complex
types and it is possible to create your own nodes.

For example, a TypeScript alias is representing a User object will look like this:

```php
use Spatie\TypeScriptTransformer\TypeScriptNodes;

new TypeScriptAlias(
    new TypeScriptIdentifier('User'),
    new TypeScriptObject([
        new TypeScriptProperty('id', new TypeScriptNumber()),
        new TypeScriptProperty('name', new TypeScriptString()),
        new TypeScriptProperty('address', new TypeScriptUnion([
            new TypeScriptString(),
            new TypeScriptNull(),
        ])),
    ]),
);
```

Transforming this alias to TypeScript will result in the following type:

```ts
type User = {
    id: number;
    name: string;
    address: string | null;
}
```

There are a lot of TypeScript nodes available, you can find them in the `Spatie\TypeScriptTransformer\TypeScript`
namespace. In the next section we'll take a look at how to build your own TypeScript nodes.
