---
title: Typing properties
weight: 2
---

Let's have a look how you can type the properties of your PHP class.

## By regular PHP types

Since PHP 7.4 it is possible to use typed properties, this package makes these types an A class citizen.

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

## By docblocks

You can also use docblocks to type properties, a more detailed overview about this can be found [here](https://docs.phpdoc.org/latest/guides/types.html).

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
}
```

It is also possible to add nullable types:

```php
class Dto
{
    /** @var ?string */
    public $string;
}
```

Type your objects:


```php
class Dto
{
    /** @var \DateTime */
    public $dateTime;
}
```

Always use a fully qualified class name, since at the moment the package cannot determine if you have imported some class:

```php
use App\DataTransferObjects\UserData;

class Dto
{
    /** @var \App\DataTransferObjects\UserData */
    public $userData; // Will work
    
    /** @var UserData */
    public $secondUserData; // Will not work
}
```


It is possible to add compound types:

```php
class Dto
{
    /** @var string|int|bool|null */
    public $compound;
}
```

Or these special types:

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

You can even reference the same type:

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

These will all transform to a `Dto` typescript type.

### Transforming arrays

The problem with arrays is that in PHP and Typescript(Javascript) they are different concepts. An array in PHP is a multi use storage structure. In Typescript a PHP array can be represented as an Array and as an Object with keys. 

Depending on how you write your annotations, the package will output an Array or Object. Let's have a look at som examples that will transform to an array:

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
    /** @var array<string, \DateTime>  */
    public $object_with_string_keys;

    /** @var array<int, \DateTime>*/
    public $object_with_int_keys;
}
```

## Combining regular types and docblocks

It is perfectly possible to combine a docblock annotation with a regular type, let's have a look:

```php
class Dto
{
    /** @var string[]  */
    public array $array;
}
```

Normally this would output the following type:

```typescript
array: Array | Array<string>
```

The package knows `string[]` is a more specific version of the `array` type so it will remove the redundant `Array` type. Now the definition becomes:

```typescript
array: Array<string>
```







