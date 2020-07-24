# Transform your PHP structures to Typescript types

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)
![Tests](https://github.com/spatie/typescript-transformer/workflows/run-tests/badge.svg)
![Styling](https://github.com/spatie/typescript-transformer/workflows/Check%20&%20fix%20styling/badge.svg)
![Psalm](https://github.com/spatie/typescript-transformer/workflows/Psalm/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)

**This package is still under heavy development, please do not use it (yet)**

Always wanted type safety within PHP and Typescript without duplicating a lot of code? Then you will like this package! Let's say you have an enum:

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

Wouldn't it be cool if you could have an automatically generated Typescript definition like this:

```typescript
export type Languages = 'typescript' | 'php';
```

This package will automatically generate such definitions for you, the only thing you have to do is adding this annotation:

```php
/** @typescript **/
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

Are you using Laravel? You're probably more interested in our Laravel specific package: [laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer).

## Support us

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us). 

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/typescript-transformer
```

## How does this work?

First, you have to configure the package. In this configuration, you define the path where your PHP classes are stored, the file where the Typescript will be written, and the transformers required to convert PHP to Typescript.

When running the package, it will look in the path you provided for classes with a `@typescript` annotation, and these classes will be given to a list of transformers who will try to convert the PHP class to Typescript. When all PHP classes are processed, the Typescript is written to a file.

## Getting started

Let's take a look at the configuration:

```php
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

$config = TypeScriptTransformerConfig::create()
    ->searchingPath(__DIR__ . '/../src') // path where your php classes are
    ->transformers([MyclabsEnumTransformer::class]) // list of transformers
    ->outputFile(__DIR__ . '/../js/generated.d.ts'); // file where Typescript will be written
```

Now we've got the package configured, let's start the transfomation process:

```php
TypescriptTransformer::create($config)->transform();
```

That's it! All the classes with a `@typescript` annotation are now converted to typescript.

Classes not converted? You probably should write some transformers. More on that later.

## Default transformers

Although writing your own transformers isn't that difficult we've added a few transformers to get started:

- `MyclabsEnumTransformer`: this converts a `myclabs\enum`
- `ClassTransformer`: an abstract transformer that can convert a class with its public properties
- `DtoTransformer`: uses the ClassTransformer and converts DTO's from the `spatie/data-transfer-object` package
- `DtoCollectionTransformer`: converts collections from the `spatie/data-transfer-object` package

When writing and transforming DTO's, I recommend you to take a look at the [data-transfer-object](https://github.com/spatie/data-transfer-object) package documentation which will explain how to type your DTO properties.

It is possible to extend the `ClassTransformer` and write your own Dto transformers, but first, let's learn how to write your own transformers!


## Writing transformers

Transformers are the heart of the package. A transformer is a class which implements the `Transformer` interface:

```php
use Spatie\TypescriptTransformer\Transformers\Transformer;

class EnumTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        // can this transformer handle the given class?
    }

    public function transform(ReflectionClass $class, string $name): Type
    {
        // get the typescript representation of the class
    }
}
```

In the `canTransform` you should decide if this transformer can convert the class. In the `transform` method, you should return a `Type`, the transformed type. Let's take a look at how we can create them.

### Creating types

```php
Type::create(
    ReflectionClass $class, // The reflection class
    string $name, // The name of the Type
    string $transformed // The Typescript representation of the class
);
```

What about creating types that depend on other types? It is possible to add a fourth argument to the `create` method:

```php
Type::create(
    ReflectionClass $class,
    string $name,
    string $transformed,
    MissingSymbolsCollection $missingSymbols
);
```

A `MissingSymbolsCollection` will contain links to other types. The package will replace these links with correct Typescript types. So, for example, you have this class:

```php
/** @typescript **/
class User extends DataTransferObject
{
    public string $name;
    
    public RoleEnum $role;
}

// And 

/** @typescript **/
class RoleEnum extends Enum
{
    const GUEST = 'guest';
    
    const ADMIN = 'admin';
}
```


When transforming this class we don't know what the `RoleEnum` will be, but since it is also converted to Typescript the package will produce the following output:

```typescript
export type RoleEnum = 'guest' | 'admin';

export type User = {
    name : string;
    role : RoleEnum;
}
```

In your transformers you should only add the symbols to the `MissingSymbolsCollection` as such:

```php
$type = $missingSymbols->add(RoleEnum::class); // Will return {%RoleEnum::class%}
```

The `add` method will return a token that can be used in your transformed type, to be replaced later. It's the link we described above between the types.

When in the end, no type was found(because it wasn't converted to Typescript, for example), it will be replaced with `any`.

#### Inline types

It is also possible to create an inline type, these types will not create a whole new Typescript type but just replace a type inline in another type. In our previous example, if we would transform `Enum` classes with an inline type, the generated Typescript would look like this:

```typescript
export type User = {
    name : string;
    role : 'guest' | 'admin';
}
```

Inline types can be created like the regular types but they do not need a name:

```php
Type::createInline(
    ReflectionClass $class,
    string $transformed
);
```

When needed you can also add a `MissingSymbolsCollection`:


```php
Type::createInline(
    ReflectionClass $class,
    string $transformed,
    MissingSymbolsCollection $missingSymbols
);
```

When you create a new transformer, do not forget to add it to the list of transformers in your configuration!


## Annotation options

When using the `@typescript` annotation, the PHP class's name will be used for the Typescript type:

```php
/** @typescript **/
class Languages extends Enum{}
```

You can also give the type another name:

```php
/** @typescript Talen **/
class Languages extends Enum{}
```

Want to define a specific transformer for the file? You can use the following annotation:

```php
/** 
 * @typescript
 * @typescript-transformer \Spatie\LaravelTypescriptTransformer\Transformers\EnumTransformer
 **/
class Languages extends Enum{}
```

## Transforming without annotations

In some cases, you want to transform classes without annotation. For example, in one of our projects, we've created resource classes which were sent to the front using DTO's. We knew these Resources would always have to be converted to Typescript, so writing the `@typescript` annotation was cumbersome.

Collectors allow you to transform classes by a specified transformer. And we're actually using a collector to collect the `@typescript` annotated classes.

A collector is a class that extends the `Collector` class, and you will have to implement two methods:

```php
class EnumCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        // Can this type be collected by this collector?
    }

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence
    {
        // Get the `ClassOccurrence` with a Transformer and name for the type
    }
}
```

First, you check if the class can be collected by this collector in `shouldCollect` when you can collect the class, `getClassOccurence` should return a correct `ClassOccurence`. A `ClassOccurence` exists of a transformer for the class, and a name the typescript type will have.

You can easily create a `ClassOccurrence` as such:

```php
ClassOccurrence::create(
    new EnumTransformer(),
    'my awsome type'
);
```

In the end you have to add the collector to your configuration:

```php
$config = TypeScriptTransformerConfig::create()
    ->collectors([EnumCollector::class])
	...
```

Collectors are checked in the order they're defined in the config, the package adds the `AnnotationsCollector` which collects `@typescript` annotated classes automatically at the end.

## Transforming Dto's

The package provides a `ClassTransformer`. The transformer will convert all the public non-static properties of a class to Typescript. But sometimes you want a bit more flexibility, for example, adding static properties or converting properties differently.

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

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
