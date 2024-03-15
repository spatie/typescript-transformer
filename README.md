# This is my package typescript-transformer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)
[![Tests](https://img.shields.io/github/actions/workflow/status/spatie/typescript-transformer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/typescript-transformer/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)

This package allows you to convert PHP classes to TypeScript.

This class...

```php
/** @typescript */
class User
{
    public int $id;
    public string $name;
    public ?string $address;
}
```

... will be converted to this TypeScript type:

```ts
export type User = {
    id: number;
    name: string;
    address: string | null;
}
```

Here's another example.

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

The `Languages` enum will be converted to:

```tsx
export type Languages = 'typescript' | 'php';
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/typescript-transformer.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/typescript-transformer)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can
support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.
You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards
on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/typescript-transformer
```

## Setting up TypeScript transformer

We first need to initialize typescript-transformer and configure what it exactly should do. If you're using Laravel,
please skip to the next section.

Since TypeScript transformer is framework-agnostic, we cannot provide you exact steps on how to integrate it into your
application. However, we can provide you with a general idea of how to do it.

Ideally, TypeScript transformer is a CLI command within your application, that can be quickly called when you need to
generate TypeScript types.

Within Symphony, for example, you can create a command like this:

```php
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class GenerateTypeScriptCommand extends Command
{
    protected static $defaultName = 'typescript:transform';

    protected function configure()
    {
        $this->setDescription('Transform TypeScript types');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = TypeScriptTransformerConfigFactory::create(); // We'll come back to this in a minute

        TypeScriptTransformer::create($config)->execute();
    }
}
```

When you've registered the command, it can be executed as such:

```bash
php bin/console typescript:transform
```

Since we haven't configured TypeScript transformer yet, this command won't do anything. Skip the Laravel section and
continue with the next section to learn how to configure TypeScript transformer.

### Laravel

When using Laravel, first install the specific `TypeScriptTransformerServiceProvider`:

```bash
php artisan typescript:install
```

This command will create a `TypeScriptTransformerServiceProvider` in your `app/Providers` directory. Which looks like
this:

```php
class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config; // We'll come back to this in a minute
    }
}
```

Now you can transform types as such:

```bash
php artisan typescript:transform
```

Since we haven't configured TypeScript transformer yet, this command won't do anything. Let's do that now.

## Running TypeScript Transformer for the first time

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
- `ClassTransformer`: Transforms PHP classes with its properties to TypeScript types
- `AttributedClassTransformer`: A special version of the `ClassTransformer` that only transforms classes with
  the `#[TypeScript]` attribute
- `LaravelClassTransformer`: A special version of the `ClassTransformer` with some goodies for Laravel users

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
transform a class, the next transformer is executed.

Transformers work on PHP classes, we need to tell TypeScript transformer where to look for these classes. This can be
done by adding a directory to the configuration:

```php
$config->watchDirectories(app_path());
```

We're almost done! The last thing we need to do is tell TypeScript transformer how to write types, this can be done by
using the `NamespaceWriter` which writes all types to a single TypeScript file with namespaces:

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

You can configure this writer and where it should put the file as such:

```php
$config->writeTypes(new NamespaceWriter(resource_path('types/generated.d.ts'))); 
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
$config->writeTypes(new ModuleWriter(resource_path('types'))); 
```

That's it! You're now ready to transform your PHP classes to TypeScript types. If you've configured
the `EnumTransformer` then, every enum should be transformed to TypeScript. When using the `AttributedClassTransformer`,
be sure to add the `#[TypeScript]` attribute to classes you want transformed.

## Making sure PHP classes are typed

The first run of TypeScript transformer might not have the desired result, a lot of property types could be `undefined`
because TypeScript transformer doesn't know what type these properties are, let's fix that!

Typescript transformer will automatically transform basic PHP types as such:

```php
class Types 
{
    public string $property; // string
    public int $property; // number
    public float $property; // number
    public bool $property; // boolean
    public mixed $property; // any
    public object $property; // object
}
```

When a type is nullable, TypeScript transformer will transform it as such:

```php
class Types 
{
    public ?string $property; // string | null
}
```

Unions and intersections are also supported:

```php
class Types 
{
    public string | int $property; // string | number
    public string & int $property; // string & number
}
```

Arrays in PHP can be transformed to two types in TypeScript, if no types are annotated, an array will become an `Array`.
When an array is typed with integer keys it will still be an Array. An array typed with string keys will become
a `Record`:

```php
class Types 
{
    public array $property; // Array
    
    /** @var bool[] */
    public array $property; // Array<boolean>

    /** @var array<int, bool> */
    public array $property; // Array<boolean>

    /** @var array<string, bool> */
    public array $property; // Record<string, boolean>
}
```

As you an see, when an array value is typed correctly, it will also be typed correctly in TypeScript.

It is also possible to use non-typical array key types, like an enum:

```php
class Types 
{
    /** @var array<PostType, string> */
    public array $property; // Record<'news'|'blog', string>
}
```

There are multiple locations where you can add property annotations:

```php
/**
* @property string[] $propertyA 
 */
class Types
{
    public array $propertyA;
    
    /** @var string[] */
    public array $propertyB;

    /**
    * @param string[] $propertyC
     */
    public function __construct(
        public array $propertyC
    ) {
    
    }
}
```

Typing objects works like magic:

```php
class Types 
{
    // App.Enums.PostType (when using the NamespaceWriter)
    // Import { PostType } from '../enums' + PostType (when using the ModuleWriter)
    public PostType $property;    
}
```

If an typed object is not transformed and thus we don't know how it will look like in TypeScript, it will be replaced
by `unknown`. It is possible to replace these unknown types with a TypeScript type, without transforming them, keep
reading to learn how to do that.

You can also type generic properties:

```php
class Types 
{
    /** @var Collection<int, string> */
    public Collection $property; // Illuminate.Support.Collection<number, string>
}
```

Properties can be made optional in TypeScript by adding the `#[Optional]` attribute:

```php
class Types 
{
    #[Optional]
    public string $property;
}
```

Transforming this class will result in the following object:

```ts
export type Types = {
    property?: string;
}
```

It is possible to hide properties from the TypeScript object by adding the `#[Hidden]` attribute:

```php
class Types 
{
    #[Hidden]
    public string $property;
}
```

When you want to replace a property type with a literal TypeScript type, you can use the `#[LiteralTypeScriptType]` attribute:

```php
class Types 
{
    #[LiteralTypeScriptType('Record<Uppercase<string>, string>')]
    public array $property;
}
```

You can also create a TypeScript object from literal types:

```php
class Types 
{
    #[LiteralTypeScriptType([
        'age' => 'number',
        'name' => 'string',
    ])]
    public array $property;
}
```

This will result in the following TypeScript object:

```ts
export type Types = {
    property: {
        age: number;
        name: string;
    };
}
```

It is also possible to type properties using php types within an attribute using the `#[TypeScriptType]` attribute:

```php
class Types 
{
    #[TypeScriptType('string')]
    public $property;
}
```

Also this attribute can be used to type an object, but this time the types can be PHP types:

```php
class Types 
{
    #[TypeScriptType([
        'age' => 'int',
        'name' => 'string',
    ])]
    public $property;
}
```

## Replacing common types

## TypeScript nodes

## Visiting TypeScript nodes

## Creating a transformer

### Extending the class Transformer

-> PropertyTypeProcessors

## Creating a typesprovider

## Formatting TypeScript

## Laravel

### Getting routes as TypeScript

## Live updates

## Advanced concepts

### Building your own Writer

### Building your own Formatter

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
