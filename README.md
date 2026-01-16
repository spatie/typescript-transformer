# TypeScript Transformer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)
[![Tests](https://img.shields.io/github/actions/workflow/status/spatie/typescript-transformer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/typescript-transformer/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)

**These docs are for the (currently in beta) v3 release of the package, you'll find the v2 docs [here](https://spatie.be/docs/typescript-transformer/v2/introduction).** 

This package allows you to convert PHP classes to TypeScript.

This class...

```php
#[TypeScript]
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
enum Languages: string
{
    case TYPESCRIPT = 'typescript';
    case PHP = 'php';
}
```

The `Languages` enum will be converted to:

```tsx
export type Languages = 'typescript' | 'php';
```

And that's just the beginning! TypeScript transformer can handle complex types, generics and even allows you to create
TypeScript functions.

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
use Spatie\TypeScriptTransformer\Enums\RunnerMode;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Console\SymfonyConsoleLogger;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTypeScriptCommand extends Command
{
    protected static $defaultName = 'typescript:transform';

    protected function configure(): void
    {
        $this->setDescription('Transform TypeScript types');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new Runner();

        $config = TypeScriptTransformerConfigFactory::create()->get(); // We'll come back to this in a minute

        return $runner->run(
            logger: new SymfonyConsoleLogger($output),
            config: $config,
            mode: RunnerMode::Direct,
        );
    }
}
```

We dive further into configuring runners later on.

When you've registered the command, it can be executed as such:

```bash
php bin/console typescript:transform
```

Since we haven't configured TypeScript transformer yet, this command won't do anything. Skip the Laravel section and
continue with the next section to learn how to configure TypeScript transformer.

### Laravel

To set up TypeScript transformer within Laravel, you'll need to install A Laravel specific package.

You can read all about it
here: [laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer).

After you've installed the package, please continue reading the next section to learn how to configure TypeScript
transformer.

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

You can configure this writer as such:

```php
$config->writer(new NamespaceWriter()); 
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
$config->writeTypes(new ModuleWriter()); 
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

### Special attributes

Classes can have attributes that change the way they are transformed, let's go through them.

Using the `#[TypeScript]` attribute is not only a way to tell typescript-transformer to transform a class, but it can
also be used to change the name of the transformed class:

```php
#[TypeScript(name: 'UserWithoutEmail')]
class User
{
    public int $id;
    public string $name;
}
```

This will transform the `User` class to `UserWithoutEmail` in TypeScript.

```ts
export type UserWithoutEmail = {
    id: number;
    name: string;
}
```

Each type will be located somewhere either being a file when using the `ModuleWriter` or in a single file when using
the `NamespaceWriter`. The location of the type can be changed by using the `#[TypeScript]` attribute:

```php
#[TypeScript(location: ['Data', 'Users'])]
class User
{
    public int $id;
    public string $name;
}
```

This will transform as such:

```ts
declare namespace Data.Users {
    export type User = {
        id: number;
        name: string;
    };
}
```

By default, the location is based on the namespace of the PHP class.

It is possible to completely remove a class from the TypeScript output by using the `#[Hidden]` attribute:

```php
#[Hidden]
enum Members: string
{
    case John = 'john';
    case Paul = 'paul';
    case George = 'george';
    case Ringo = 'ringo';
}
```

This is particularly useful when using the `EnumTransformer` and you want to hide certain enums from the TypeScript.

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

As you can see, when an array value is typed correctly, it will also be typed correctly in TypeScript.

It is also possible to use non-typical array key types, like an enum:

```php
class Types 
{
    /** @var array<PostType, string> */
    public array $property; // Record<'news'|'blog', string>
}
```

It is possible to define array shapes like this:

```php
class Types 
{
    /** @var array{age: int, name: string} */
    public array $property; // { age: number, name: string }
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

If a typed object is not transformed and thus we don't know how it will look like in TypeScript, it will be replaced
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

When you want to replace a property type with a literal TypeScript type, you can use the `#[LiteralTypeScriptType]`
attribute:

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

This attribute also can be used to type an object, but this time the types can be PHP types:

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

Some PHP classes should be transformed into something atypical, an example of this is the `DateTime` class. When you
send such an object to the front it will be represented by a string rather than an object. TypeScript transformer allows
you to replace these kinds types with an appropriate TypeScript type.

Replacing types can be done in the config:

```php
$config->replaceType(DateTime::class, 'string');
```

Now all `DateTime` objects will be transformed to a string in TypeScript. This also includes inherited classes
like `Carbon`, those will also be transformed to a string.

When using an interface like `DateTimeInterface` you can also replace it with a TypeScript type:

```php
$config->replaceType(DateTimeInterface::class, 'string');
```

All classes that implement `DateTimeInterface` will be transformed to a string in TypeScript.

### Replacements

As we've seen before it is possible to replace types by writing them out like you would do in an annotation, this allows
you to build complex types, for example:

```php
$config->replaceType(DateTimeInterface::class, 'array{day: int, month: int, year: int}');
```

From now on, all `DateTimeInterface` objects will be replaced by the following TypeScript object:

```ts
{
    day: number;
    month: number;
    year: number;
}
```

It is also possible to define a replacement as an internal TypeScript node(more on that later):

```php
$config->replaceType(DateTimeInterface::class, new TypeScriptString());
```

Or use a closure to define the replacement:

```php
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;

$config->replaceType(DateTimeInterface::class, function (TypeReference $reference) {
    return new TypeScriptString();
});
```

## TypeScript nodes

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
namespace. In the advanced section we'll take a look at how to build your own TypeScript nodes.

## Creating a transformer

Transformers are the most important part of TypeScript transformer, they take a PHP class and try to transform it to a
TypeScript type. A transformer implements the `Transformer` interface:

```php
interface Transformer
{
    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable;
}
```

The `TransformationContext` contains all the information you need to transform a class:

```php
class TransformationContext
{
    public function __construct(
        // The name for the class that is being transformed, can be user defined
        public string $name,
        // The segments of the namespace where the class is located
        public array $nameSpaceSegments,
    ) {
    }
}
```

Within the method a `Transformed` data object should be created and returned which looks like this:

```php
use Spatie\TypeScriptTransformer\References\ClassStringReference;

new Transformed(
    // The TypeScript node representing the transformed class
    typeScriptNode: $typeScriptNode,
    // A unique name for the transformed class for internal package use
    reference: new ClassStringReference($reflectionClass->getName()),
    // A location where the class should be written to
    // By default, this is the namespace of the class and the $nameSpaceSegments from the TransformationContext can be used
    location: $context->nameSpaceSegments,
    // Whether the type should be exported in TypeScript
    export: true,
);
```

If a class cannot be transformed, the `Untransformable` object should be returned:

```php
use Spatie\TypeScriptTransformer\Untransformable;

Untransformable::create();
```

When a class cannot be transformed, the next transformer in the list will be executed.

### Extending the class Transformer

Most of the time, transforming a class comes down to taking all the properties and transforming them to a TypeScript
object with properties, the package provides an easy-to-extend class for this called `ClassTransformer`.

You can create your own version by extending the `ClassTransformer` and implementing the `shouldTransform` method:

```php
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

class MyTransformer extends ClassTransformer
{
    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return $reflection->implementsInterface(\Spatie\LaravelData\Data::class);
    }
}
```

In the case above, the transformer will only run when transforming classes which are data objects from
the [laravel-data](https://github.com/spatie/laravel-data) package. We encourage you to overwrite certain methods so
that the transformer fits your needs.

#### Choosing properties to transform

By default, all public non-static properties of a class are transformed, but you can overwrite the `properties` method
to change this:

```php
protected function getProperties(PhpClassNode $phpClassNode): array
{
    return $phpClassNode->getProperties(ReflectionProperty::IS_PUBLIC|ReflectionProperty::IS_PROTECTED);
}
```

#### Optional properties

It is possible to make a property optional in TypeScript by overwriting the `isPropertyReadonly` method:

```php
protected function isPropertyOptional(
    PhpPropertyNode $phpPropertyNode,
    PhpClassNode $phpClassNode,
    TypeScriptNode $type,
    TransformationContext $context,
): bool {
    return str_starts_with($phpPropertyNode->getName(), '_');
}
```

By default, we check whether a property has an `#[Optional]` attribute.

#### Readonly properties

You can make a property readonly by overwriting the `isPropertyReadonly` method:

```php
protected function isPropertyReadonly(
    PhpPropertyNode $phpPropertyNode,
    PhpClassNode $phpClassNode,
    TypeScriptNode $type,
): bool {
   return str_ends_with($phpPropertyNode->getName(), 'Read');
}
```

By default, we check whether a property was made readonly in PHP.

#### Hiding properties

It is possible to completely hide a property from the TypeScript object by overwriting the `isPropertyHidden` method:

```php
protected function isPropertyHidden(
    PhpPropertyNode $phpPropertyNode,
    PhpClassNode $phpClassNode,
    TypeScriptProperty $property,
): bool {
    return count($phpPropertyNode->getAttributes(Hidden::class)) > 0;
}
```

By default, we check whether a property has an `#[Hidden]` attribute.

#### Class property processors

Sometimes a more fine-grained control is needed over how a property is transformed, this is where class property
processors come to play. They allow you to update the TypeScript Node of the property, you can create them by
implementing the `ClassPropertyProcessor` interface:

```php
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;

class RemoveNullProcessor implements ClassPropertyProcessor
{
    public function execute(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        if ($property->type instanceof TypeScriptUnion) {
            $property->type = new TypeScriptUnion(
                array_values(array_filter($property->type->types, fn (TypeScriptNode $type) => !$type instanceof TypeScriptNull))
            );
        }

        return $property;
    }
}
```

You can add these processors to the transformer by overwriting the `classPropertyProcessors` method:

```php
protected function classPropertyProcessors(): array
{
    return [
        new RemoveNullProcessor(),
    ];
}
```

A class property processor can also be used to remove properties from the TypeScript object:

```php
class RemoveAllStrings implements ClassPropertyProcessor
{
    public function execute(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property
    ): ?TypeScriptProperty {
        if ($property->type instanceof TypeScriptString) {
            return null;
        }

        return $property;
    }
}
```

## Creating a TransformedProvider

Until now we've only taken a look at transforming PHP classes to TypeScript, but what if you want to transform something
else? This is where the `TransformedProvider` comes into play, it is a class that provides TypeScript types and other
structures. The transformers we've seen before are actually bundled in a default `TransformerProvider` provided by the
package.

A `TransformedProvider` implements the `TransformedProvider` interface:

```php
namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface TransformedProvider
{
    /**
     * @return array<Transformed>
     */
    public function provide(
        TypeScriptTransformerConfig $config,
    ): array;
}
```

The `provide` method is called when the TypeScript transformer is executed, it should return `Transformed` objects.

We could for example add a generic type which transforms Laravel collections:

```php
class AddLaravelCollectionProvider implements TransformedProvider
{
    public function provide(
        TypeScriptTransformerConfig $config,
    ): array {
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

### Using different writers in providers

Currently, TypeScript transformer will output all transformed objects using the default writer configured. While most of
the time this is sufficient, there are cases where you might want to have more control over how certain transformed
objects are written down.

For example, in the Laravel package all types from classes, enums, and interfaces are written to a single file using
namespaces. Yet the package also provide some helper functions allowing you to generate URLs for your routes. These
helper functions are best written to a separate file without namespaces.

In order to achieve this, TypeScript transformer allows you to use different writers per transformed object, you can
set a writer on a transformed object as such:

```php
$transformed->setWriter(new ModuleWriter());
```

Please notice that you can only set writers on a transformed object once and we advise you for performance reasons
to create a writer in the constructor of your provider and reuse it for all transformed objects within that provider.

It is still possible to reference transformed objects written down by writers from other providers or the default
writer, TypeScript transformer will take care of linking them together and generating the correct import statements.

### Logging in providers

TypeScript transformer provides a logging mechanism that can be used within providers to log messages during the
transformation process. This is particularly useful for debugging and tracking the transformation flow and displaying
errors or other important
information.

When implementing the `LoggingTransformedProvider` interface, the `setLogger` method receives a `Logger` instance as an
additional parameter:

```php
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;

class CustomTransformedProvider implements LoggingTransformedProvider, TransformedProvider
{
    protected Logger $logger;

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function provide(
        TypeScriptTransformerConfig $config,
    ): array {
        $this->logger->info('Starting transformation process in CustomTransformedProvider.');

        // Some of 

        $this->logger->info('Finished transformation process in CustomTransformedProvider.');
    }
}
```

A log always exists of an item which can be any type which is JSON encodable and an optional title for context:

```php
$this->logger->debug($transfomed->reference, 'Transformed reference details');
$this->logger->info($transfomed->typeScriptNode, 'TypeScript node details');
$this->logger->warning($transfomed->getName(), 'Potential issue with transformed item');
$this->logger->error($transfomed->changed, 'Error encountered during transformation');
```

## Referencing types

Types sometimes reference other types like PHP classes referencing other PHP classes. Within the package a concept of
references is used to link these types together.

When creating `Transformed` objects we've always used the `ClassStringReference` since we were referencing PHP classes,
sometimes you might be transforming something which is not a PHP class for example a list of strings. In this case, you
can use a `CustomReference`:

```php
use Spatie\TypeScriptTransformer\References\CustomReference;

new Transformed(
    typeScriptNode: new TypeScriptAlias(
        new TypeScriptIdentifier('Type'),
        new TypeScriptUnion([new TypeScriptLiteral('PHP'), new TypeScriptLiteral('TypeScript')]),
    ),
    reference: new CustomReference('my_languages_package', 'some_languages'),
    location: ['App', 'Languages'],
);
```

A custom reference should be unique for each type, that's why it is built up from a group and a name. We advise you when
creating a package (or if you're implementing a feature within your app) to choose a custom group name in order not to
conflict with other packages.

In the end the transformed TypeScript will look like this:

```ts
namespace App.Languages {
    export type Type = 'PHP' | 'TypeScript';
}
```

It is possible to reference this type in another `Transformed` object:

```php
new Transformed(
    typeScriptNode: new TypeScriptAlias(
        new TypeScriptIdentifier('Compiler'),
        new TypeScriptObject([
            new TypeScriptProperty('type', new CustomTypeReference('my_languages_package', 'some_languages')),
        ]),
    ),
    reference: new ClassStringReference(Compiler::class),
    location: ['App', 'Compilers'],
);
```

The transformed TypeScript now will look like this:

```ts
namespace App.Compilers {
    export type Compiler = {
        type: App.Languages.Type;
    };
}
```

Since we're using the same reference, the package is smart enough to link them together when transforming to TypeScript.

Off course, you can also reference PHP classes in the same way:

```php
new Transformed(
    typeScriptNode: new TypeScriptAlias(
        new TypeScriptIdentifier('Post'),
        new TypeScriptObject([
            new TypeScriptProperty('publisher', new TypeScriptReference(new ClassStringReference(User::class))),
        ]),
    ),
    reference: new ClassStringReference(User::class),
    location: ['App', 'Models'],
);
```

## Formatting TypeScript

The package tries to format the transformed TypeScript as good as possible, but sometimes this could be far from
perfect. That's why it is possible to automatically format the TypeScript code after transforming.

By default, the package has support for two formatters:

- `PrettierFormatter`: Formats the TypeScript code using Prettier
- `EslintFormatter`: Formats the TypeScript code using ESLint

You can add a formatter to the configuration like this:

```php
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;

$config->formatter(new PrettierFormatter());
```

It is possible to create your own formatter by implementing the `Formatter` interface:

```php
interface Formatter
{
    public function format(array $files): void;
}
```

The `$files` array contains the TypeScript files that need to be formatted, you can format them in any way you like.

## Advanced concepts

The package is highly configurable and can be extended in many ways, let's take a look at some advanced concepts.

### Building your own TypeScript node

The package comes with a lot of TypeScript nodes, but sometimes it might be necessary to build your own.

A TypeScript node is a regular PHP class that implements the `TypeScriptNode` interface:

```php
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class PickNode implements TypeScriptNode, TypeScriptNamedNode
{
    public function __construct(
        private TypeScriptNode $type,
        private array $properties,
    ) {}

    public function write(WritingContext $context): string
    {
        return 'Pick<' . $this->type->write($context) . ', ' . implode(' | ', $this->properties) . '>';
    }
    
    public function getName(): string
    {
        return 'Pick';
    }
}
```

The write method is responsible for transforming the TypeScript node to a string, the `WritingContext` object is passed
to lower level TypeScript nodes to reference other TypeScript types and can generally be ignored.

Some TypeScript nodes represent a type with a name like an interface, enum, ... these nodes should implement
the `TypeScriptNamedNode` interface. The `getName` method should return the name of the TypeScript node so that it can
be referenced by other TypeScript nodes.

When you've got a node which itself contains another TypeScript node that can be a `TypeScriptNamedNode` we recommend
you to implement `TypeScriptForwardingNamedNode`. This interface requires you to implement the `getForwardedNamedNode`
method which should return the TypeScript node that either is another `TypeScriptForwardingNamedNode`
or `TypeScriptNamedNode`. An example of such a node is the `TypeScriptAlias`:

```php
class TypeScriptAlias implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    public function __construct(
        public TypeScriptIdentifier|TypeScriptGeneric $identifier,
        public TypeScriptNode $type,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "type {$this->identifier->write($context)} = {$this->type->write($context)};";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
```

Lastly, the package also provides some tooling to visit a tree of all TypeScript nodes, when your custom node is
encapsulating other TypeScript nodes you should implement the `TypeScriptVisitableNode` interface which requires you to
implement the visitorProfile method.

The `visitorProfile` method should return a `VisitorProfile` object which contains information about the properties
of your TypeScript node PHP class that can be visited. We extinguish two types of properties: single nodes properties
containing a single node and iterable properties containing a set of properties.

The `TypeScriptGeneric` node is an excellent example:

```php
class TypeScriptGeneric implements TypeScriptForwardingNamedNode, TypeScriptNode, TypeScriptVisitableNode
{
    /**
     * @param  array<TypeScriptNode>  $genericTypes
     */
    public function __construct(
        public TypeScriptIdentifier|TypeReference $type,
        public array $genericTypes,
    ) {
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('type')->iterable('genericTypes');
    }

    // ....
}
```

It contains a single node property `type` and an iterable property `genericTypes`. From now on the package will visit
the nodes within these properties.

### Visiting TypeScript nodes

When working with TypeScript nodes in a class property processor or a custom TypeScript node, it might be necessary to
visit and alter nodes in the tree. The `Visitor` class can be used to visit such a tree of TypeScript
nodes.

The visitor will start in a node and then traverse the tree of TypeScript nodes, it is possible to register a `before`
and `after` callback for each node it visits. The `before` callback is called before visiting the children of a node and
the `after` callback is called after visiting the children of a node.

```php
use Spatie\TypeScriptTransformer\Visitor\Visitor;

Visitor::create()
    ->before(function (TypeScriptNode $node){
        echo 'Before visiting ' . $node::class . PHP_EOL;
    })
    ->after(function (TypeScriptNode $node) {
        echo 'After visiting ' . $node::class . PHP_EOL;
    })
    ->execute($rootNode);
```

When running the visitor on the following node:

```php
$rootNode = new TypeScriptUnion([
    new TypeScriptString(),
    new TypeScriptNumber(),
]);
```

The output will be (redacted for readability):

```
Before visiting TypeScriptUnion
Before visiting TypeScriptString
After visiting TypeScriptString
Before visiting TypeScriptNumber
After visiting TypeScriptNumber
After visiting TypeScriptUnion
```

By default, the visitor will visit the tree of nodes and run the callback on each node within the tree. It is possible
to limit the types of nodes the callback runs on:

```php
Visitor::create()
    ->after(function (TypeScriptUnion $node, [TypeScriptUnion::class]) {
        // Do something with TypeScriptUnion nodes
    })
    ->execute($rootNode);
```

When not returning a TypeScript node from the callback, the visitor will continue traversing the tree. It is possible to
replace a node in the tree like this:

```php
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

Visitor::create()
    ->after(function (TypeScriptUnion $node, [TypeScriptUnion::class]) {
        if(count($node->types) === 1) {
            return VisitorOperation::replace(array_values($node->types)[0]);
        }  
    })
    ->execute($rootNode);
```

The visitor above will replace all union nodes with a single type with that type.

It is also possible to remove a node from the tree:

```php
Visitor::create()
    ->after(function (TypeScriptString $node, [TypeScriptString::class]) {
        return VisitorOperation::remove();
    })
    ->execute($rootNode);
```

### Hooking into TypeScript transformer

Every time the TypeScript transformer is executed, it will go through a series of steps, it is possible to run a visitor
in between some of these steps.

The steps look as following:

1. Running of the TransformedProviders creating a collection of Transformed objects
2. Running other providers to add extra Transformed objects
3. Possible hooking point: `providedVisitorHook`
4. Connecting references between Transformed objects
5. Possible hooking point: `connectedVisitorHook`
6. Create a collection of WriteableFiles
7. Write those files to disk
8. Format the files

The two hooking points below can be used to run a visitor on the collection of Transformed objects:

```php
use Spatie\TypeScriptTransformer\Visitor\VisitorClosureType;

$config->providedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::Before
);

$config->connectedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::Before
);
```

Running visitors as an after hook is also possible:

```php
$config->providedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::After
);

$config->connectedVisitorHook(
    fn(TransformedCollection $collection) => Visitor::create()->execute($collection),
    [TypeScriptUnion::class],
    VisitorClosureType::After
);
```

### Using the EnumTransformer

The package ships with a built-in EnumTransformer which can transform PHP enums to TypeScript enums or union types.

It is possible to configure this transformer to generate enums for other types of enums than the native PHP enums by
implementing a custom `EnumProvider`:

```php
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\EnumProvider;

use Spatie\Enum\Enum;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class SpatieEnumProvider implements EnumProvider
{
    public function isEnum(PhpClassNode $phpClassNode): bool
    {
        return $phpClassNode->reflection->isSubclassOf(Enum::class);
    }

    public function isValidUnion(PhpClassNode $phpClassNode): bool
    {
        return true;
    }

    public function resolveCases(PhpClassNode $phpClassNode): array
    {
        /** @var class-string<Enum> $className */
        $className = $phpClassNode->getName();

        return array_map(
            fn (Enum $enum) => [
                'name' => $enum->value,
                'value' => $enum->value,
            ],
            $className::cases()
        );
    }
}
```

The `isEnum` method should return whether the provided class node is an enum, the `isValidUnion` method should return
whether the enum can be transformed to a union type rather than a TypeScript enum. The `resolveCases` method should
return an array of cases for the enum which is a mapping of case names to their values.

Within your configuration you can register the transformer as such:

```php
$config->transformer(
    new EnumTransformer(enumProvider: new SpatieEnumProvider())
);
```

### Building your own Writer

Writers are responsible for writing out the TypeScript types, the package comes with three writers:

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

It is possible to create your own writer by implementing the `Writer` interface:

```php
use Spatie\TypeScriptTransformer\Data\WriteableFile;

interface Writer
{
/**
     * @param array<Transformed> $transformed
     *
     * @return array<WriteableFile>
     */
    public function output(
        array $transformed,
        TransformedCollection $transformedCollection,
    ): array;

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference|GlobalNamespaceResolvedReference;
}
```

The `output` method should return an array of `WriteableFile` objects, these objects contain the TypeScript
code and the path relative to the configured output directory where the file should be stored.

The parameters for output are an array of `Transformed` objects and the full `TransformedCollection` while intuitively
these structures seem similar, the array of `Transformed` objects only contains the objects that need to be written by
this writer, while the`TransformedCollection` contains all transformed objects being handled by the package. This comes
in handy when needing to resolve references to other transformed objects not being written by this writer.

In order to reference to other transformed objects you'll need an identifier to be used within the TypeScript code. For
the GlobalNamespaceWriter this will be a fully qualified name like 'App.Models.User' while for the ModuleWriter this
will be an import statement like `import { User } from '../models/User'` and then an identifier like `User`.

In order to resolve these references we strongly recommend you to use the `ResolveImportsAndResolvedReferenceMapAction`
which takes the current path the writer is currently writing to, the array of transformed objects to be written by
this writer in the current file and the full transformed collection:

```php
use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;

[$imports, $resolvedReferenceMap] = (new ResolveImportsAndResolvedReferenceMapAction())->execute(
    currentPath: $currentPath, // e.g. 'app/models/index.ts'
    transformed: $transformed,
    transformedCollection: $transformedCollection,
);
```

The action returns a tuple containing, the collection of imports needed for the current file which can be transformed
into TypeScript import nodes like this:

```php
$imports->getTypeScriptNodes()
```

The action also returns a resolved reference map which is a mapping of the transformed reference key to their TypeScript
identifier, this map should be provided to the `WritingContext` a structure required for writing TypeScript nodes.

We now can write out the imports and transformed objects like this:

```php
$output = '';

foreach ($imports->getTypeScriptNodes() as $import) {
    $output .= $import->write($writingContext).PHP_EOL;
}

foreach ($location->transformed as $transformedItem) {
    $output .= $transformedItem->write($writingContext).PHP_EOL;
}

$writeableFile = new WriteableFile($filePath, $output);
```

In the end the writer then should return an array of all the `WriteableFile` objects it created.

### Configuring TypeScript identifier name resolution

The `ResolveImportsAndResolvedReferenceMapAction` uses a default strategy to resolve TypeScript identifiers from all the
transformed references. It works like this:

1. If the identifier was written by a GlobalNamespaceWriter, the fully qualified name is used as the identifier.
2. If no identifier in the module exists with the same name, the transformed name is used as the identifier.
3. If an identifier in the module already exists with the same name, a Import suffix is added to the transformed name.
4. If the Import suffix name also exists, a numeric suffix is added until a unique name is found.

It is possible to customize this strategy by providing a Closure returning a unique identifier for the current
identifier to be used:

```php
new ResolveImportsAndResolvedReferenceMapAction(
    moduleImportNameResolver: fn(string $identifier, array $usedIdentifiers): string => $identifier . uniqid(),
);
```

The first parameter is the identifier that would be used by default and the second parameter is an array of already used
identifiers within the current module. Every time the closure is called the array of used identifiers is updated with
the previously returned identifiers.

## Watching changes and live updating TypeScript

TypeScript transformer can run in a watch mode where it will watch for changes in your PHP files and automatically
regenerate the TypeScript files when a change is detected.

> While this is possibly one of the coolest features of the package it is still heavily experimental and might not work
> in all environments and not always as expected. Feel free to open issues when you encounter problems with a demo
> project and
> an exact plan of steps to reproduce the issue.

### How does it work?

In order to be able to watch for changes, the package uses chokidar, a package that is able to watch for file changes in
a
cross-platform way. You'll need to install it as such:

```bash
npm install chokidar
```

Or using yarn:

```bash
yarn add chokidar
```

When running the package in watch mode, it will start a master process which will start a worker process, the idea here
is that the master process always keeps running while the worker process can be restarted when a change is detected
which requires a full application reload.

In order to not always having to reload the full application (and thus starting a new worker) on every file change, the
worker process will smartly swap the Reflection instances used throughout TypeScript transformer when a file change is
detected.

This is the reason why we don't provide Reflection* instances throughout the package, but rather wrapper classes around
these instances. The initial data is loaded by PHP's Reflection API, but afterwards the `roave/better-reflection`
package is used to create in-memory representations of the changed classes.

To make it easy to swap these different types of Reflection instance the package provides Php*Node wrapper classes like:

- PhpClassNode
- PhpPropertyNode
- PhpMethodNode
- PhpParameterNode
- And more...

When your provider however needs something else than the reflection instances provided by these wrapper classes, for
example, checking the container for an instance or the router for the current application routes, you'll need to restart
the worker process in order to get the updated state of your application. We'll come back to this later.

### Watching changes in a Laravel project

When you're using Laravel you can run the following command to start watching for changes:

```bash
php artisan typescript:transform --watch
```

### Setting up the runner for watching changes

Laravel users can skip the following section as the Laravel package already has built-in support for watching changes.

In the beginning of this documentation we saw how to create a command using a runner to transform TypeScript types. In
order to enable watching changes we'll need to set up the runner a bit differently:

```php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTypeScriptCommand extends Command
{
    protected static $defaultName = 'typescript:transform';

    protected function configure(): void
    {
        $this
            ->setDescription('Transform TypeScript types')
            ->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for file changes')
            ->addOption('worker', null, InputOption::VALUE_NONE, 'Run as worker process');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new Runner();

        $config = TypeScriptTransformerConfigFactory::create()->get();

        return $runner->run(
            logger: new SymfonyConsoleLogger($output),
            config: $config,
            mode: match ([$input->getOption('watch'), $input->getOption('worker')]) {
                [false, false] => RunnerMode::Direct,
                [true, false] => RunnerMode::Master,
                [true, true] => RunnerMode::Worker,
                default => throw new \Exception('A worker only needs to be started in watch mode.'),
            },
            workerCommand: fn (bool $watch) => 'bin/console typescript:transform --worker '.($watch ? '--watch ' : ''),
        );
    }
}
```

The command above adds two options: `--watch` and `--worker`. The `--watch` option is used to start the master process
which watches for file changes, the `--worker` option is used internally by the master process to start the worker
process.

Feel free to adjust the `workerCommand` closure to your own command structure so that the master process can start the
worker correctly.

### Refactoring with an IDE or AI Agent

When running multiple changes throughout your codebase in multiple files at once like refactoring a class name in
PHPStorm or letting Claude code make multiple changes, the watcher might not be able to pick up these changes
up to ten seconds after they were made. This is due to the fact that most IDEs and AI Agents make changes in a temporary
file and then move the temporary file to the original file location.

In the end TypeScript transformer will pick up these changes, but it might take a bit longer than expected.

### Letting a provider hook into watch events

The package will now watch for file changes in the `watchDirectories` you've configured earlier. But what about
providers
which provide additional transformed objects outside of the package transformation flow?

These providers can implement the `WatchableTransformedProvider` interface:

```php
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;

class CustomWatchableProvider implements WatchingTransformedProvider, TransformedProvider
{
    public function directoriesToWatch(): array
    {
    
        return [__DIR__.'/models'];
    }

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): ?WatchEventResult
    {
        if(! $watchEvent instanceof SummarizedWatchEvent){
            return null;
        }
        
        // Handle changes to the TransformedCollection
    }
}
```

The `directoriesToWatch` method should return an array of directories the provider wants to watch for changes. The
`handleWatchEvent` method is called when a change is detected in one of these directories.

There are a few possible types of watch events:

- `FileCreatedWatchEvent`: A file was created
- `FileUpdatedWatchEvent`: A file was updated
- `FileDeletedWatchEvent`: A file was deleted
- `DirectoryDeletedWatchEvent`: A directory was deleted

All of these events extend the `WatchEvent` class and contain the path of the changed file or directory.

In the end when all events have been processed, a `SummarizedWatchEvent` is dispatched containing all the created,
updated and deleted files and directories.

When everything went well, the `handleWatchEvent` method should return null or WatchEventResult::continue(). When
your provider needs an updated application state e.g. a complete worker restart, it should return WatchEventResult::
completeRefresh().

Please note that when a complete refresh is requested, the whole worker process will be restarted which could take a
few seconds depending on the size of your application. In order to counter this we, for example, in the Laravel package
needed a complete new application state for the routes helper provider since it needs to fetch all routes from the
router. Instead of requesting a complete refresh on every route change, we created a custom command returning all the
routes as JSON allowing us to update the routes without restarting the whole application by calling this command within
the `handleWatchEvent` method.

#### Updating the transformed collection

Within the `handleWatchEvent` method you receive the current `TransformedCollection` which you can update based on the
detected changes. Since a lot of the transformed objects within the collection are referenced by each other it can
become quite complex to make sure everything is updated correctly.

That's why the collection provides a few methods to make this process easier:

**public function add(Transformed ...$transformed): void**

Allows you to add new transformed objects to the collection, if a transformed object with the same reference already
exists it will be replaced.

**public function has(Reference|string $reference): bool**

Allows you to check whether a transformed object with the given reference exists in the collection.

**public function get(Reference|string $reference): ?Transformed**

Allows you to get a transformed object by its reference, returns null when no transformed object was found?

**public function remove(Reference|string $reference): void**

Removes a transformed object from the collection by its reference. If that transformed object is referenced by other
transformed objects these references will be removed as well and tagged to be missing. If later on a new transformed
object is added with the same reference these missing references will be resolved automatically.

**public function findTransformedByFile(string $path): ?Transformed**

Allows you to find the current transformed object for a given file path, useful when a file was updated. The way this
works is that each Reference implementation can implement the `FilesystemReference` interface which provides the file
path of the referenced object.

**public function findTransformedByDirectory(string $path): Generator**

Allows you to find all transformed objects within a given directory.

**public function requireCompleteRewrite(): void**

While we try to cache the output of transformed objects as much as possible, only invalidating changed objects and the
objects that reference changed objects, sometimes a complete rewrite is necessary. You can request such a complete
rewrite
by calling this method.

### Loggers

The package provides some loggers that can be used to log messages during the transformation process, out of the box the
package provides the following loggers:

- `ArrayLogger`: Logs messages to an array which can be retrieved later
- `SymfonyConsoleLogger`: Logs messages to the Symfony Console output
- `NullLogger`: A logger that logs nothing
- `RayLogger`: Logs messages to Ray (https://spatie.be/docs/ray)
- `MultiLogger`: A logger that logs messages to multiple loggers

The Laravel package provides an additional logger:

- `LaravelConsoleLogger`: Logs messages to the Laravel console output

A logger can be configured when constructing the `Runner`.

Implementing your own logger is possible by implementing the `Logger` interface:

```php
namespace Spatie\TypeScriptTransformer\Support\Loggers;

interface Logger
{
    public function debug(mixed $item, ?string $title = null): void;

    public function info(mixed $item, ?string $title = null): void;

    public function warning(mixed $item, ?string $title = null): void;

    public function error(mixed $item, ?string $title = null): void;
}
```

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
