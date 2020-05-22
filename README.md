# Transform your PHP structures to Typescript types

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/typescript-transformer/run-tests?label=tests)](https://github.com/spatie/typescript-transformer/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)

Want type safety within PHP and Typescript without duplicating a lot of code? Then you will like this package! Let's say you have a enum:

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

Wouldn't it be cool if you could have an automatically generated Typescript structure like this:

```typescript
export type Languages = 'typescript' | 'php';
```

This package will automatically generate such structures for you, the only thing you have to do is adding this annotation:

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

First you configure the path where your PHP structures are stored, the package searches through this path and collects all the PHP classes with a `@typescript` annotation. For each class in this collection a tranformer is searched, this transformer will convert a PHP class to Typescript. It is possible to define your own transformers, more on that later. When each class is transformed to typescript, the package will write out the typescript files in a location configured by you.

## Getting started

First we need to configure the package:

```php
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;

$config = new TypeScriptTransformerConfig(
    '../src', // path where your php classes are,
    [MyclabsEnumTransformer::class], // an array of transformers,
    'types.d.ts', // the default typescript_output file,
    '../js'// $the_output_path_where_typescript_files_will_be_stored
);
```

Now we've got the package configured, let's transform those classes into typescript:

```php
TypescriptTransformer::create($config)->transform();
```

That's it! All the classes with a `@typescript` annotation are now converted to typescript.

## Annotation options

When using the `@typescript` annotation, the name of the PHP class will be used for the Typescript type and the type will be stored in the default file you defined in your config:

```php
/** @typescript **/
class Languages extends Enum{}
```

You can also give the type another name:

```php
/** @typescript Talen **/
class Languages extends Enum{}
```

Or write the type to another file(make sure this file always has a `.ts` extension):

```php
/** @typescript admin/types.d.ts **/
class Languages extends Enum{}
```

And off course you can combine these directives giving the type a custom name and file:

```php
/** @typescript Talen admin/types.d.ts **/
class Languages extends Enum{}
```

## Writing transformers

Transformers are the heart of the package, we've added a default one in the package for the `myclabs/enum` enum, but you're probably going to write some transformers yourself.

A transformer is a class which implements the `Transformer` interface:

```php
use Spatie\TypescriptTransformer\Transformers\Transformer;

class EnumTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        // can this transformer handle the givven class?
    }

    public function transform(ReflectionClass $class, string $name): string
    {
        // get the typescript representation of the class
    }
}
```

After creating a transformer, do not forget to add them to the transformers in your configuration!

You can override the transformer of a class by adding following annotation:

```php
/** 
 * @typescript
 * @typescript-transformer \Spatie\TypescriptTransformer\MyclabsEnumTransformer           
 */
class Languages extends State{}
```

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
