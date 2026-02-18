---
title: Typing properties
weight: 4
---

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
    // App.Enums.PostType (when using the GlobalNamespaceWriter)
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

Want to make all properties optional? You can do that by adding the `#[Optional]` attribute to the class:

```php
#[Optional]
class Types
{
    public string $property;
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

When your literal type references types from other files, you can add additional imports:

```php
use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;

class Types
{
    #[LiteralTypeScriptType(
        'Record<string, SomeComponent>',
        additionalImports: [
            new AdditionalImport(__DIR__.'/../types/components.ts', 'SomeComponent'),
        ]
    )]
    public array $property;
}
```

This generates the correct import statement and resolves the name, including aliasing when there are conflicts. You can import multiple names from the same file:

```php
new AdditionalImport(__DIR__.'/../types/components.ts', ['SomeComponent', 'OtherThing'])
```

### Referencing other transformed types

When your literal type needs to reference other PHP types that are also being transformed, use the `references` parameter with `%placeholder%` syntax:

```php
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

class Types
{
    #[LiteralTypeScriptType(
        'Record<string, %User%> | %Post%[]',
        references: [
            'User' => UserData::class,
            'Post' => PostData::class,
        ]
    )]
    public array $property;
}
```

Each placeholder `%Name%` in the TypeScript string will be replaced with the resolved type name. This integrates into the full reference graph — imports are generated in module mode, aliases are resolved when there are naming conflicts, and missing references are tracked.

You can also pass custom `Reference` objects:

```php
use Spatie\TypeScriptTransformer\References\CustomReference;

#[LiteralTypeScriptType(
    '%Custom% | null',
    references: [
        'Custom' => new CustomReference('group', 'name'),
    ]
)]
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
