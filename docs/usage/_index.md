---
title: Usage
weight: 1
---

The package provides a `DtoTransformer`. The transformer will convert all the public non-static properties of a class to Typescript. But sometimes you want a bit more flexibility, for example, adding static properties or converting properties differently.

In such cases you can create your own `ClassTransformer`:

```php
class DtoTransformer extends ClassTransformer
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
            new CleanupClassPropertyProcessor(),
            new LaravelCollectionClassPropertyProcessor(),
            new LaravelDateClassPropertyProcessor(),
            new ApplyNeverClassPropertyProcessor(),
        ];
    }
}
```

First of all, `canTransform` still returns if a transformer can handle the type. We've overwritten the `resolveProperties`, so we're now not excluding static properties anymore.

This Transformer comes from the [laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer) package, as you can see, we're defining some `ClassPropertyProcessors`. These processors will be used to change the types for a property and run before any missing symbols are replaced. 

In the default package we provide two processors:

- `CleanupClassPropertyProcessor` removes some unneeded types from the property and is recommended to run first
- `ApplyNeverClassPropertyProcessor` when a property is not well-typed, `never` is used as Typescript type so you know your properties can be better typed

Specifically for Laravel we include the following processors in the Laravel package:

- `LaravelCollectionClassPropertyProcessor` since Laravel has a `Collection` type which we replace with the `array` type
- `LaravelDateClassPropertyProcessor` Laravel will output instances of `Carbon` and `DateTimeInterface` as `strings` so we change properties with these types accordingly

A property processor is a class that implements `ClassPropertyProcessor`:

```php
class LaravelCollectionClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty
    {
        // Transform the types of the property
    }
}
```

In the `process` method, you should adapt the `ClassProperty` to your needs and return it. With a `ClassProperty` you have access to two arrays: `$types` and `$arrayTypes`, these arrays contain strings of possible types like `bool`, `int`, `string`, `null` or even types like `Enum::class`.

The `$types` array has the types that a property can have, which are not stored in an array, the `$arrayTypes` array will have the types that are stored within an array of the property. For example, a property like this:

```php
/** @var string|string[] **/
public $emails;
```

Will have `$arrayTypes = ['string']` and `$types = ['string', 'null']` since the property technically can be `null.

Let's create a property processor that removes all the `null` types from a property:

```php
class RemoveNullClassPropertyProcessor implements ClassPropertyProcessor
{
    public function process(ClassProperty $classProperty): ClassProperty
    {
        $classProperty->types = $this->replaceProperties($classProperty->types);
        $classProperty->arrayTypes = $this->replaceProperties($classProperty->arrayTypes);

        return $classProperty;
    }

    private function replaceProperties(array $properties): array
    {
        return array_unique(array_filter(
            $properties,
            fn (string $property) => $property !== null
        ));
    }
}
```

Now do not forget to create your own `ClassTransformer` with this `RemoveNullClassPropertyProcessor` in the `getClassPropertyProcessors` method.

When you need more information about the property, you can also get some additional information about it by using the `$reflection` which stores the `ReflectionProperty`.
