---
title: Running TypeScript Transformer for the first time
weight: 2
---

TypeScript transformer is a highly configurable framework to transform PHP classes and more into TypeScript types, we
provide some highly used functionality out of the box, but you can configure it to your needs.

We're going to start with transforming basic PHP classes to TypeScript types, this is what the package actually does:

1. It starts searching for PHP classes within your application
2. It makes a ReflectionClass from each of these found classes
3. These ReflectionClasses are then processed by a list of transformers (they take a ReflectionClass and try to make a
   TypeScript type from it)
4. If a ReflectionClass is transformed, it is added to a list to be written to TypeScript otherwise the class is ignored
5. That list is then written to a TypeScript file

Transformers are the most important part in this whole process, they implement the `Transformer` interface which looks
like this:

```php
interface Transformer
{
    public function transform(ReflectionClass $reflectionClass, TransformationContext $context): Transformed|Untransformable;
}
```

By default, the package comes with a few transformers:

- `EnumTransformer`: Transforms PHP enums to TypeScript enums
- `ClassTransformer`: Transforms PHP classes with its properties to TypeScript types (abstract, read on for more info)
- `AttributedClassTransformer`: A special version of the `ClassTransformer` that only transforms classes with the
  `#[TypeScript]` attribute
- `InterfaceTransformer`: Transforms PHP interfaces to TypeScript interfaces

If you're using our Laravel package, you also get access to:

- `LaravelAttributedClassTransformer`: A special version of the `ClassTransformer` with some goodies for Laravel users

You're free to mix and match these transformers to your needs, or even create your own transformers.

Registering can be done as such within your TypeScript CLI command or `TypeScriptTransformerServiceProvider` (if you're
using Laravel):

```php
$config->transformer(AttributedClassTransformer::class);
```

Since transformers are just PHP classes, you can also pass them arguments when initializing them:

```php
$config->transformer(new EnumTransformer(useNativeEnums: true)); // transformers enums as TypeScript native enums and not as a union of strings
```

Quick note: transformers are executed in the order they are registered in the configuration, when a transformer cannot
transform a class, the next transformer is checked.

Transformers work on PHP classes, we need to tell TypeScript transformer where to look for these classes. This can be
done by adding a directory to the configuration:

```php
$config->transformDirectories(app_path());
```

We're almost done! The last thing we need to do is tell TypeScript transformer how to write types. The package comes with three writers:

**GlobalNamespaceWriter**

Generates a single TypeScript file which exports all types to the global namespace. That namespace is based upon the
location of the type. The GlobalNamespaceWriter will always generate a declation TypeScript file with a `.d.ts`
extension which
means that only types are allowed in this file and executable code is prohibited.

**FlatModuleWriter**

Creates one module for all transformed objects in a single TypeScript file. The file will contain all transformed
objects (types & executable code) without any namespaces.

**ModuleWriter**
An extension of the FlatModuleWriter which creates a file per location. Each file will contain all transformed objects (
types & executable code) for that location without any namespaces.

This can be done by
using the `GlobalNamespaceWriter` which writes all types to a single TypeScript file with namespaces:

```ts
declare namespace App.Data {
    export type PostData = {
        title: string;
        slug: string;
        type: App.Enums.PostType;
        tags: Array<string>;
        publish_date: string | null;
        published: boolean;
    };
}
declare namespace App.Enums {
    export type PostType = 'news' | 'blog';
}
```

You can configure this writer as such:

```php
$config->writer(new GlobalNamespaceWriter());
```

The directory where the types should be written to needs to be configured as well:

```php
$config->outputDirectory(__DIR__.'/generated');
```

If you want a file per namespace, then you can use the `ModuleWriter`, it will write a structure like this:

```ts
// app/data/index.d.ts
export type PostData = {
    title: string;
    slug: string;
    type: App.Enums.PostType;
    tags: Array<string>;
    publish_date: string | null;
    published: boolean;
};

// app/enums/index.d.ts
export type PostType = 'news' | 'blog';
```

You can configure it like this:

```php
$config->writer(new ModuleWriter());
```

That's it! You're now ready to transform your PHP classes to TypeScript types. If you've configured
the `EnumTransformer` then running following command:

```
// on Symphony
php bin/console typescript:transform

// on Laravel
php artisan typescript:transform
```

Should transform every enum into TypeScript. When using the `AttributedClassTransformer`, be sure to add the
`#[TypeScript]` attribute to classes you want transformed.
