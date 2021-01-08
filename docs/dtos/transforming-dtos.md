---
title: Transforming DTOs
weight: 1
---

The package provides a `DtoTransformer` out of the box. This transformer will convert all public non-static properties of a class to TypeScript types. 

Let's take a look at an example:

```php
/** @typescript */
class User
{
    public int $id;

    public string $name;

    public ?string $address;

    /** @var string[] */
    public array $emails;
}
```

This will be transformed to:

```tsx
export type User = {
    int: number;
    name: string;
    address: string | null;
    array: Array<string>;
}
```

In the next chapters, we'll look at how to adapt the `DtoTransformer` to be a perfect match for your project.

A small side note for the Laravel developers: you should use the `DtoTransformer` from the `spatie/laravel-typescript-transformer` package. This transformer has some extra niceties for Laravel projects you don't want to miss!

### Replacing simple types

In the end, the package can only output types that are `string`, `bool`, `int`, `double`, `array`, and references to other types (for example, another DTO). You will have to replace some types in your DTOs with more primitive versions. This is normal behavior since you can only communicate these primitive types with the frontend through JSON.
 
For example you cannot send a `DateTime` object to the frontend, you will probably be sending a string representation of the `DateTime` object.

Although it is possible to take full control of the `DtoTransformer` and you can change the behavior of the property type replacements to primitive types to your own liking. Sometimes you want to replace some simple types to TypeScript. For example, a `DateTime` or `Carbon` object will probably always be a `string` in your TypeScript definition.

It is possible to define these simple replacements in the config:

```php
TypeScriptTransformerConfig::create()
    ->defaultTypeReplacements([
        DateTime::class => 'string',
    ])
    ...
```

In the above example, we replace all `DateTime`s with a `string` type. Any type you can use as a class property's `@var` annotation can be used as a replacement. 

For example, should you want to represent a `DateTime` as an array of strings, then that's perfectly possible:
 
```php
TypeScriptTransformerConfig::create()
    ->defaultTypeReplacements([
        DateTime::class => 'string[]',
    ])
    ...
```
 
If you want to convert to a literal/specific TypeScript type you can use the `TypeScriptType` class like this:

```php
TypeScriptTransformerConfig::create()
    ->defaultTypeReplacements([
        DateTime::class => TypeScriptType::create('SomeGenericType<string>'),
    ])
    ...
```

### Writing your own DTO Transformers

Sometimes you'll want even more flexibility. For example when adding static properties to the transformed properties or when replacing properties with primitive types. In these cases you can create your own `DtoTransformer`:

```php
class DtoTransformer extends DtoTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), DataTransferObject::class);
    }
    
    protected function resolveProperties(ReflectionClass $class): array
    {
        return array_values($class->getProperties(ReflectionProperty::IS_PUBLIC));
    }

    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
            new LaravelCollectionTypeProcessor(),
            new DtoCollectionTypeProcessor(),
        ];
    }
}
```

First of all, `canTransform` still returns `true` if a transformer can handle the type conversion. In this specific example, we created a transformer that will only transform DTOs extending from our `spatie/data-transfer-object` package. We've also overwritten the `resolveProperties` method to not exclude static properties.

As you can see, we're defining some `TypeProcessors`. These processors will be used to replace the type of a property with a more primitive version and will run before any missing symbols are replaced. [You can read more about type processors here](https://docs.spatie.be/typescript-transformer/v2/dtos/changing-types-with-type-processors/).

When creating your own `DtoTransformer`, do not forget to register it in your configuration!
