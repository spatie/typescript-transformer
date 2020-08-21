---
title: Transforming
weight: 1
---

The package provides a `DtoTransformer` which will convert all the public non-static properties of a class to Typescript. Let's take a look at an example:

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

```typescript
export type User = {
    int: number;
    name: string;
    address: string | null;
    array: Array<string>;
}
```

In the next chapters we take a look how to adapt the `DtoTransformer` so it will be a perfect match for your project.

A small side note for the Laravel developers: you should use the `DtoTransformer` from the `spatie/laravel-typescript-transformer` package. This transformer has some extra niceties for Laravel projects you don't want to miss!

### Replacing simple types

In the end the package can only output types that are `string`, `bool`, `int`, `double`, `array` and references to other types(for example another DTO). You will have to replace some types in your Dto's with more primitive versions. This is actually normal behaviour since you can only communicate these primitive types with the frontend through JSON.
 
For example: you cannot send a `DateTime` object to the frontend, you will probably be sending a string representation of the `DateTime` object.

Although it is possible to take full control of the `DtoTransformer` and you can change the behaviour of the property type replacements to primitive types to your own liking. Sometimes you just want to replace some simple types to Typescript. For example a `DateTime` or `Carbon` object will probably always be `string` in your Typescript definition.

It is possible to define such simple replacements in the config:

```php
TypeScriptTransformerConfig::create()
    ->classPropertyReplacements([
        DateTime::class => 'string',
    ])
    ...
```

Here we replace all DateTime's with a string. You can write any type you can also write into a class property's `@var` annotation. Should you want to represent a DateTime as an array of string then that's perfectly possible:
 
```php
TypeScriptTransformerConfig::create()
    ->classPropertyReplacements([
        DateTime::class => 'string[]',
    ])
    ...
```
 
If you want to convert to a specific Typescript type you can do the following:

```php
TypeScriptTransformerConfig::create()
    ->classPropertyReplacements([
        DateTime::class => TypescriptType::create('SomeGenericType<string>'),
    ])
    ...
```

### Writing your own DTO Transformers

Sometimes you want a bit more flexibility, for example, adding static properties to the transformed properties. Or replacing properties with primitive types differently. In such cases you can create your own `DtoTransformer`:

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

    protected function getClassPropertyProcessors(): array
    {
        return [
            new ReplaceDefaultTypesClassPropertyProcessor(
                $this->config->getClassPropertyReplacements()
            ),
            new DtoCollectionClassPropertyProcessor(),
            new ApplyNeverClassPropertyProcessor(),
        ];
    }
}
```

First of all, `canTransform` still returns if a transformer can handle the type, in this example we created a transformer that will only transform DTO's from our `spatie/data-transfer-object` package. We've also overwritten the `resolveProperties`, so we're not excluding static properties anymore.

As you can see, we're defining some `ClassPropertyProcessors`. These processors will be used to replace the type of a property with a more primitive version. And will run before any missing symbols are replaced. You can read more about them [here](https://docs.spatie.be/typescript-transformer/v1/dtos/class-property-processors/)

When creating your own `DtoTransformer`, do not forget to register it in your configuration!
