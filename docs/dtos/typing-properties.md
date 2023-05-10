---
title: Typing properties
weight: 1
---

Let's take a look at how we can type individual properties of a PHP class.

## Using PHP's built-in typed properties

It's possible to use typed properties in a class. This package makes these types an A-class citizen.

```php
class Dto
{
    public string $string;

    public int $integer;

    public float $float;

    public bool $bool;

    public array $array;
    
    public mixed $mixed;
}
```

It is also possible to use nullable types:

```php
class Dto
{
    public ?string $string;
}
```

You can even use these union types:

```php
class Dto
{
    public float|int $float_or_int;
}
```

Or use other types that can be replaced:

```php
class Dto
{
    public DateTime $datetime;
}
```

## Using attributes

You can use one of the two attributes provided by the package to transform them to TypeScript directly, more information about this [here](https://spatie.be/docs/typescript-transformer/v2/usage/annotations#using-typescript-within-php).

## Using docblocks

You can also use docblocks to type properties. You can find a more detailed overview of this [here](https://docs.phpdoc.org/latest/guides/types.html). While PHP's built-in typed properties are fine, docblocks allow for a bit more flexibility:

```php
class Dto
{
    /** @var string */
    public $string;

    /** @var int */
    public $integer;

    /** @var float */
    public $float;

    /** @var bool */
    public $bool;

    /** @var array */
    public $array;
    
    /** @var array|string */
    public $arrayThatMightBeAString;
}
```

It is also possible to use nullable types in docblocks:

```php
class Dto
{
    /** @var ?string */
    public $string;
}
```

And add types for your (custom) objects:


```php
class Dto
{
    /** @var \DateTime */
    public $dateTime;
}
```

Note: always use the fully qualified class name (FQCN). At this moment, the package cannot determine imported classes used in a docblock:

```php
use App\DataTransferObjects\UserData;

class Dto
{
    /** @var \App\DataTransferObjects\UserData */
    public $userData; // FCCN: this will work
    
    /** @var UserData */
    public $secondUserData; // Won't work, class import is not detected
}
```

It's also possible to add compound types:

```php
class Dto
{
    /** @var string|int|bool|null */
    public $compound;
}
```

Or these unusual PHP specific types:

```php
class Dto
{
    /** @var mixed */
    public $mixed; // transforms to `any`
    
    /** @var scalar */
    public $scalar; // transforms to `string|number|boolean`
    
    /** @var void */
    public $void; // transforms to `never`
}
```

You can even reference the object's own type:

```php
class Dto
{
    /** @var self */
    public $self;
    
    /** @var static */
    public $static;
    
    /** @var $this */
    public $void;
}
```

These will all transform to a `Dto` TypeScript type.

### Transforming arrays

Arrays in PHP and TypeScript (JavaScript) are entirely different concepts. This poses a couple of problems we'll address. A PHP array is a multi-use storage/memory structure. In TypeScript, a PHP array can be represented both as an `Array` and as an `Object` with specified keys.

Depending on how your annotations are written, the package will output either an `Array` or `Object`. Let's have a look at some examples that will transform into an `Array` type:

```php
class Dto
{
    /** @var \DateTime[] */
    public $array;

    /** @var array<\DateTime> */
    public $another_array;

    /** @var array<string|int,\DateTime> */
    public $you_probably_wont_write_this;
}
```

You can type objects as such:

```php
class Dto
{
    /** @var array<string,\DateTime> */
    public $object_with_string_keys;

    /** @var array<int,\DateTime> */
    public $object_with_int_keys;
}
```

## Combining regular types and docblocks

Whenever a property has a docblock, that docblock will be used to type the property. The 'real' PHP type will be omitted.

If the property is nullable and has a docblock that isn't nullable, then the package will make the TypeScript type nullable.

## Optional types

You can make certain properties of a DTO optional in TypeScript as such:

```php
class DataObject extends Data
{
    public function __construct(
        #[Optional]
        public int $id,
        public string $name,
    )
    {
    }
}
```

This will be transformed into:

```tsx
{
    id? : number;
    name : string;
}
```

You can also transform all properties in a class to optional, by adding the attribute to the class:

```php
#[Optional]
class DataObject extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    )
    {
    }
}
```

Now all properties will be optional:

```tsx
{
    id? : number;
    name? : string;
}
```

## Hidden types

You can make certain properties of a DTO hidden in TypeScript as such:

```php
class DataObject extends Data
{
    public function __construct(
        public int $id,
        #[Hidden]
        public string $hidden,
    )
    {
    }
}
```

This will be transformed into:

```tsx
{
    id : number;
}
```
