# Transform your PHP structures to Typescript types

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/typescript-transformer/run-tests?label=tests)](https://github.com/spatie/typescript-transformer/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)

**This package is still under heavy development, please do not use it (yet)**

Always wanted type safety within PHP and Typescript without duplicating a lot of code? Then you will like this package! Let's say you have a enum:

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

Using Laravel? You're probably more interested into our Laravel specific package: [laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer).

## Support us

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us). 

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/typescript-transformer
```

## How does this work?

First you have to configure the package. In this configuration you define the path where your PHP classes are stored, the file where the Typescript will be written and the transformers required to convert PHP to Typescript. You can write your own transformers but more on that later...

When running the package it will look in your PHP path for classes with a `@typescript` annotation, these classes will be given to a list of transformers who will try to convert the PHP class to typescript. In the end when all PHP classess are processed the typescript is written to the default file.

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

Classes not converted? You probably should write some transformers, read on!

## Default transformers

Altough writing your own transformers isn't that difficult we've added a few tranformers so you can get started:

- MyclabsEnumTransformer: this one converts a `myclabs\enum`
- ClassTransformer: an abstract transformer that can transform a class with it's public properties
- DtoTransformer: uses the ClassTransformer and transforms DTO's from the `spatie/data-transfer-object` package
- DtoCollectionTransformer: converts collections from the `spatie/data-transfer-object` package




## Writing transformers

Transformers are the heart of the package, we've added a few default ones in the package, but you're probably going to need to write some transformers yourself.

A transformer is a class which implements the `Transformer` interface:

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

### Creating types

In the `transform` method you should convert a PHP class to a `Type` object:

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

A `MissingSymbolsCollection` will contain defintions to other types, the package will replace these missing types with correct Typescript types when running. So for example, you have this class:

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


When transforming this class we don't know exactly what the RoleEnum will be, but since it is also converted to typescript the package will produce following output:

```typescript
export type RoleEnum = 'guest' | 'admin';

export type User = {
    name : string;
    role : RoleEnum;
}
```

When in the end no type was found(because it wasn't converted to typescript for example), it will be replaced with `any`.

#### Inline types

It is also possible to create an inline type, these types will not create a whole new Typescript type but just replace a type inline in another type. In our previous example, if we replaced Enums with an inline type, the generated Typescript would look like this:

```typescript
export type User = {
    name : string;
    role : 'guest' | 'admin';
}
```

Inline types can be created like the regulat types:

```php
Type::create(
    ReflectionClass $class,
    string $name,
    string $transformed,
    MissingSymbolsCollection $missingSymbols
);
```


When you create a new transformer, do not forget to add it to the list of transformers in your configuration!


## Annotation options

When using the `@typescript` annotation, the name of the PHP class will be used for the Typescript type:

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

In some cases you want to transform classes without annotation, for example in one of ours projects we've created resource classes that were sent to the front using DTO's. We knew these Resources would always have to be converted to Typescript so writing the `@typescript` annotation was a bit cumbersome.

Collectors allow you to transform classes by a specified transformer, we're actually using a collector to collect the `@typescript` annotated classes.

A collector is a class that extebds the `Collector` class, you will have to implement two methods:

```php
class EnumCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), Enum::class);
    }

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence
    {
        return ClassOccurrence::create(
            new MyclabsEnumTransformer(),
            $class->getShortName()
        );
    }
}
```

First you check if the class can be collected by this collector in `shouldCollect` when you can collect the class, `getClassOccurence` should return a correct `ClassOccurence`. A `ClassOccurence` exists of a transformer for the class and a name the typescript type will have.

In the end you have to add the collector to your configuration:

```php
$config = TypeScriptTransformerConfig::create()
    ->collectors([EnumCollector::class])
	...
```

Collectors are checked in the order they're defined in the config, we add the `AnnotationsCollector` which collects `@typescript` annotated classes automatically at the end.

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
