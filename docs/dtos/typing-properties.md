---
title: Typing properties
weight: 2
---

Let's take a look at how we can type individual properties of a PHP class.

## Using PHP's built-in typed properties

Since PHP 7.4 it's possible to use typed properties in a class. This package makes these types an A-class citizen.

```php
class Dto
{
    public string $string;

    public int $integer;

    public float $float;

    public bool $bool;

    public array $array;
}
```

It is also possible to use nullable types:

```php
class Dto
{
    public ?string $string;
}
```

Or use other types that can be replaced:

```php
class Dto
{
    public DateTime $datetime;
}
```

## Using docblocks

You can also use docblocks to type properties. A more detailed overview of this can be found [here](https://docs.phpdoc.org/latest/guides/types.html). While PHP's built-in typed properties are great, docblocks allow for a bit more flexibility:

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

Note: always use the fully qualified class name (FCCN). At this moment the package cannot determine imported classes used in a docblock:

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

Or these special PHP specific types:

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

Arrays in PHP and TypeScript (JavaScript) are completely different concepts. This poses a couple of problems we'll address. A PHP array is a multi-use storage/memory structure. In TypeScript, a PHP array can be represented both as an `Array` and as an `Object` with specified keys. 

Depending on how your annotations are written the package will output either an `Array` or `Object`. Let's have a look at some examples that will transform into an `Array` type:

```php
class Dto
{
    /** @var \DateTime[] */
    public $array;

    /** @var array<\DateTime> */
    public $another_array;

    /** @var array<string|int,\DateTime> */
    public $you_propably_wont_write_this;
}
```

Typing objects can be done as such:

```php
class Dto
{
    /** @var array<string, \DateTime> */
    public $object_with_string_keys;

    /** @var array<int, \DateTime> */
    public $object_with_int_keys;
}
```

## Combining regular types and docblocks

It is possible and recommended combine regular type with docblock annotations for more specific typing. Let's have a look:

```php
class Dto
{
    /** @var string[] */
    public array $array;
}
```

The package knows `string[]` is a more specific version of the `array` type and will internally remove the redundant `Array` type. The outputted type definition looks like this:

```tsx
array: Array<string>
```
