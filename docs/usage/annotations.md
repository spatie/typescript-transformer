---
title: Describing types
weight: 3
---

PHP classes will only be converted to TypeScript when they are annotated, there are quite a few ways to do this, let's take a look.

When using the `@typescript` annotation, the PHP class's name will be used as name for the TypeScript type:

```php
/** @typescript */
class Language extends Enum{
    const en = 'en';
    const nl = 'nl';
    const fr = 'fr';
}
```

The package will produce the following TypeScript:

```tsx
export type Language = 'en' | 'nl' | 'fr';
```

It is also possible to use a PHP8 attribute like this:

```php
#[TypeScript]
class Language extends Enum{
    const en = 'en';
    const nl = 'nl';
    const fr = 'fr';
}
```

You can also give the type another name:

```php
/** @typescript Talen **/
class Language extends Enum{}
```

Which also can be done using attributes:

```php
#[TypeScript('Talen')]
class Language extends Enum{}
```

Now the transformed TypeScript looks like this:

```tsx
export type Talen = 'en' | 'nl' | 'fr';
```

## Inlining types

It is also possible to annotate types as an inline type. These types will not create a whole new TypeScript type but replace a type inline in another type. Let's create a class containing the `Language` enum:

```php
/** @typescript **/
class Post
{
    public string $name;
    public Language $language;
}
```

The transformed version of a `Post` would look like this:

```tsx
export type Language = 'en' | 'nl' | 'fr';

export type Post = {
    name : string;
    language : Language;
}
```

We could inline the `Language` enum as such:

```php
/** 
 * @typescript 
 * @typescript-inline           
 */
class Language extends Enum{}
```

Or using an attribute:

```php
#[TypeScript]
#[InlineTypeScriptType]
class Language extends Enum{}
```

And now our transformed TypeScript would look like this:

```ts
export type Post = {
    name : string;
    language : 'en' | 'nl' | 'fr';
}
```

## Using TypeScript to write TypeScript

It is possible to directly represent a type as TypeScript within your PHP code:

```php
#[TypeScript]
#[LiteralTypeScriptType("string | null")]
class CustomString{}
```

Now when `Language` is being transformed, the TypeScript respresentation is used:

```tsx
export type CustomString = string | null;
```

You can even provide an array of types:

```php
#[TypeScript]
#[LiteralTypeScriptType([
    'email' => 'string',
    'name' => 'string',
    'age' => 'number',
])]
class UserData{
    public $email;
    public $name;
    public $age;
}
```

This would transform to:

```tsx
export type UserData = {
    email: string;
    name: string;
    age: number;
};
```

This attribute can also be used with properties in a class, for example:

```php
#[TypeScript]
class Post
{
    public string $name;
    
    #[LiteralTypeScriptType("'en' | 'nl' | 'fr'")]
    public Language $language;
}
```

## Using PHP types to write TypeScript

When you have a very specific type you want to describe in PHP then you can use the `TypeScriptType` which can transform every type [phpdocumentor](https://www.phpdoc.org) can read. For example, let's say you have an array that always has the same keys as this one:

```php
$user = [
    'name' => 'Ruben Van Assche',
    'email' => 'ruben@spatie.be',
    'age' => 26,
    'language' => Language::nl()
];
```

When we put that array as a property in a class:

```php
#[TypeScript]
class UserRepository{
    public array $user;
}
```

The transformed type will look like this:

```tsx
export type UserRepository = {
    user: Array;
};
```

We can do better than this, since we know the keys of the array:

```php
use Spatie\TypeScriptTransformer\Attributes\TypeScript;#[TypeScript]
class UserRepository{
    #[TypeScriptType([
        'name' => 'string',
        'email' => 'string',
        'age' => 'int',
        'language' => Language::class
    ])]
    public array $user;
}
```

Now the transformed TypeScript will look like this:

```tsx
export type UserRepository = {
    user: {
        name: string;
        email: string;
        age: number;
        language: 'en' | 'nl' | 'fr';
    };
};
```

As you can see, the package is smart enough to convert `Language::class` to an inline enum we defined earlier.

## Generating `Record` types

If you need to generate a `Record<K, V>` type, you may use the `RecordTypeScriptType` attribute:

```php
use Spatie\TypeScriptTransformer\Attributes\RecordTypeScriptType;

class FleetData extends Data
{
    public function __construct(
        #[RecordTypeScriptType(AircraftType::class, AircraftData::class)]
        public readonly array $fleet,
    ) {
    }
}
```

This will generate a `Record` type with a key type of `AircraftType::class` and a value type of `AircraftData::class`:

```ts
export type FleetData = {
  fleet: Record<App.Enums.AircraftType, App.Data.AircraftData>
}
```

Additionally, if you need the value type to be an array of the specified type, you may set the third parameter of `RecordTypeScriptType` to `true`:

```php
class FleetData extends Data
{
    public function __construct(
        #[RecordTypeScriptType(AircraftType::class, AircraftData::class, array: true)]
        public readonly array $fleet,
    ) {
    }
}
```

This will generate the following interface:

```ts
export type FleetData = {
  fleet: Record<App.Enums.AircraftType, Array<App.Data.AircraftData>>
}
```

## Selecting a transformer

Want to define a specific transformer for the file? You can use the following annotation:

```php
/** 
 * @typescript
 * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer::class
 */
class Languages extends Enum{}
```

It is also possible to transform types without adding annotations. You can read more about it [here](https://spatie.be/docs/typescript-transformer/v2/usage/selecting-classes-using-collectors).
