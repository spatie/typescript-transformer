---
title: Getting started
weight: 1
---

Until now we've only taken a look at transforming PHP classes to TypeScript, but what if you want to transform something
else? This is where the `TransformedProvider` comes into play, it is a class that provides TypeScript types and other
structures. The transformers we've seen before are actually bundled in a default `TransformerProvider` provided by the
package.

A `TransformedProvider` implements the `TransformedProvider` interface:

```php
namespace Spatie\TypeScriptTransformer\TransformedProviders;

interface TransformedProvider
{
    /**
     * @return array<Transformed>
     */
    public function provide(): array;
}
```

The `provide` method is called when the TypeScript transformer is executed, it should return `Transformed` objects.

We could for example add a generic type which transforms Laravel collections:

```php
class AddLaravelCollectionProvider implements TransformedProvider
{
    public function provide(): array
    {
        $type = new Transformed(
            typeScriptNode: new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('Collection'),
                    [new TypeScriptIdentifier('T')],
                ),
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('Array'),
                    [new TypeScriptIdentifier('T')],
                ),
            ),
            reference: new ClassStringReference(Collection::class),
            location: ['Illuminate', 'Support']
        ));

        return [$type];
    }
}
```

When we register the provider as such in the configuration:

```php
$config->provider(new AddLaravelCollectionProvider());
```

Our transformed TypeScript will have the following type:

```ts
namespace Illuminate.Support {
    export type Collection<T> = Array<T>;
}
```

When referencing a Laravel collection in one of our PHP classes like this:

```php
class Data
{
    /** @var Collection<string>  */
    public Collection $collection;
}
```

The transformed TypeScript will look like this:

```ts
export type Data = {
    collection: Illuminate.Support.Collection<string>;
}
```
