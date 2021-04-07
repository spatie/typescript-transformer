---
title: General overview
weight: 1
---

Let's look at a real-world use case of how the package can transform PHP types to TypeScript. We're not going to use the default Laravel resources because they cannot be typed. Instead, we're going to use the [spatie/data-transfer-object](https://github.com/spatie/data-transfer-object) package.

Let's first create a `UserResource`:

```php
class UserResource extends DataTransferObject implements Arrayable
{
    public ?int $age = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?AddressResource $address = null;
}
```

Here is the code of `AddressResource`:

```php
class AddressResource extends DataTransferObject implements Arrayable
{
    public ?string $street = null;

    public ?string $number = null;

    public ?string $city = null;

    public ?string $postal = null;

    public ?string $country = null;
}
```

Each property is nullable, so it's easy to send an empty instance to the front end where necessary.

To easily convert a user to a `UserResource`, we're going to add a static `make` function to it. We'll also implement `Illuminate\Contracts\Support\Arrayable` so the resource can be converted to an array when sending it to the front end. This interface requires the object to have a `toArray` method. The implementation of the `toArray` method lives in the `DataTransferObject` base class, which will use the object's public properties.

When applying the changes described, the `UserResource` will now look like this:

```php
use Illuminate\Contracts\Support\Arrayable;

class UserResource extends DataTransferObject implements Arrayable
{
    public ?int $age = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?AddressResource $address = null;

    public static function make(User $user): self
    {
        return new static([
            'age' => $user->age,
            'name' => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'address' => AddressResource::make($user->address ?? new Address()),
        ]);
    }
}
```

Let's also apply the same changes to the `AddressResource`.

```php
class AddressResource extends DataTransferObject implements Arrayable
{
    public ?string $street = null;

    public ?string $number = null;

    public ?string $city = null;

    public ?string $postal = null;

    public ?string $country = null;

    public static function make(Address $address): self
    {
        return new self([
            'street' => $address->street,
            'number' => $address->number,
            'city' => $address->city,
            'postal' => $address->postal,
            'country' => $address->country,
        ]);
    }
}
```

When using DTO's, it's impossible to assign a `string` to an `int` type. Another benefit is IDE completion. You can now construct your resource with all the information hinted by your IDE.

## Using resources in your project

Let's use the `UserResource` in a controller.

```php
class UserController
{
    public function create()
    {
        return UserResource::make(new User());
    }

    public function update(User $user)
    {
        return UserResource::make($user);
    }
}
```

## Transforming DTOs to TypeScript

```php
/** @typescript */
class UserResource extends DataTransferObject implements Arrayable
{
    // ...
}

/** @typescript */
class AddressResource extends DataTransferObject implements Arrayable
{
    // ...
}
```

With that annotation in place, we can generate the typescript equivalents by executing this command:


```bash
php artisan typescript:transform
```

Then we get the following output:

```bash
+------------------------------------+------------------------------------+
| PHP class                          | TypeScript entity                  |
+------------------------------------+------------------------------------+
| App\Http\Resources\UserResource    | App.Http.Resources.UserResource    |
| App\Http\Resources\AddressResource | App.Http.Resources.AddressResource |
+------------------------------------+------------------------------------+
Transformed 2 PHP types to TypeScript

```

A new file was created in the `resources/js` directory of our application. `generated.ts` contains two types:

```ts
namespace App.Http.Resources {
    export type AddressResource = {
        street: string | null;
        number: string | null;
        city: string | null;
        postal: string | null;
        country: string | null;
    }

    export type UserResource = {
        age: number | null;
        name: string | null;
        email: string | null;
        address: App.Http.Resources.AddressResource | null;
    }
}
```

These types can now be used in TypeScript code. Referencing a `UserResource` can now be done using `App.Http.Resource.UserResource`.

## Using collectors to find resources

Instead of manually adding `@typescript` to each class, we can use a [collector](https://spatie.be/docs/typescript-transformer/v2/usage/selecting-classes-using-collectors).

Let's first create an abstract class Resource:

```php
abstract class Resource extends DataTransferObject implements Arrayable
{
}
```

Next, the `UserResource` and `AddressResource` should extend `Resource`:

```php
class UserResource extends Resource
{
   // ...
}


class AddressResource extends Resource
{
   // ...
}
```

With that in place, we can create a collector that will process all classes that extend `Resource`

```php
class ResourceCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Resource::class);
    }
    
     public function getTransformedType(ReflectionClass $class): ?TransformedType
     {
        if(! $class->isSubclassOf(Resource::class))
        {
            return null;
        }
     
        $transformer = new DtoTransformer($this->config);
        
        return $transformer->transform(
            $class,
            Str::before($class->getShortName(), 'Resource')
        );
     }
}
```

Finally, `ResourceCollector` should be added to the list of collectors in the configuration file `typescript-transformer.php`:

```php
   ...

    /*
     * Collectors will search for classes in your `searching_path` and choose the correct
     * transformer to transform them. By default, we include an AnnotationCollector
     * which will search for @typescript annotated classes to transform.
     */

    'collectors' => [
        Spatie\TypeScriptTransformer\Collectors\AnnotationCollector::class,
        App\Support\TypeScriptTransformer\ResourceCollector::class,
    ],
    
    ...
```

Now you can run `php artisan typescript:transform` to create the TypeScript definitions.

### Using default type replacements

You can specify to which TypeScript type a PHP type should be converted.

Let's add a `$birthday` property to the `UserResource`, which is of type `Carbon`.

```php
class UserResource extends DataTransferObject implements Arrayable
{
    public ?int $age = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?AddressResource $address = null;
    
    public ?Carbon $birthday = null;

    public static function make(User $user): self
    {
        return new self([
            'age' => $user->age,
            'name' => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'address' => AddressResource::make($user->address ?? new Address()),
            'birthday' => $user->birthday,
        ]);
    }
}
```

Since `Carbon` isn't a primitive type like a `string`, `int`, `bool`, `array`, we actually cannot send it directly to the front. Using the `Resource` class, we can convert the `Carbon` instance to a string:

```php
abstract class Resource extends DataTransferObject implements Arrayable
{
    public function toArray(): array
    {
        return collect(parent::toArray())->map(function ($value) {
            if ($value instanceof Carbon) {
                return $value->toAtomString();
            }

            return $value;
        })->toArray();
    }
}
```

This class will transform it to the `any` TypeScript type, but you can make it more specific.  You can specify to which TypeScript type the PHP type should be converted to in the' typescript-transformer' config file.

```
   ...

    'default_type_replacements' => [
        // ...
        Carbon::class => 'string',
    ],
    
    ...
```

### Using transformer to manually convert a type

In the previous section, we converted a `Carbon` type to a string. If you want to have fine-grained control over how a PHP type should be converted to a TypeScript type, you can use a `Transformer`. Let's convert `Carbon` to a type that has a day, month, and year.

First, we need to create a PHP class that has the desired structure.

```php
@typescript
class CustomDate
{
    private int $year;

    private int $month;

    private int $day;

    public function __construct(int $year, int $month, int $day)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public function get(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'is_today' => date('Y/m/d') === "{$this->year}/{$this->month}/{$this->day}"
        ];
    }
}
```

Next, the `UserResource` needs to use the `CustomDate` type:

```php
class UserResource extends Resource
{
    public ?int $age = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?AddressResource $address = null;

    public ?CustomDate $birthday = null;

    public static function make(User $user): self
    {
        return new self([
            'age' => $user->age,
            'name' => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'address' => AddressResource::make($user->address ?? new Address()),
            'birthday' => new CustomDate(
                $user->birthday->year, 
                $user->birthday->month, 
                $user->birthday->day
            ),
        ]);
    }
}
```

And the base `Resource` needs to be aware of the `CustomDate` as well.

```php
abstract class Resource extends DataTransferObject implements Arrayable
{
    public function toArray(): array
    {
        return collect(parent::toArray())
           ->map(function ($value) {
               if ($value instanceof CustomDate) {
                   return $value->get();
               }

               return $value;
           })
           ->toArray();
    }
}
```

If we would run `php artisan typescript:transform` now, this would be the result.

```ts
export type User = {
    age: number | null;
    name: string | null;
    email: string | null;
    address: App.Http.Resources.Address | null;
    birthday: any | null;
}
```

That `any` does not describe our homemade `CustomDate` type. Let's fix that by using a `Transformer`:

```php
class CustomDateTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if(!$class->getName() === CustomDate::class)
        {
            return null;
        }
        
        $type = <<<EOT
export type {$name} = {
    year: number;
    month: number;
    day: number;
    is_today: boolean;
}
EOT;

        return TransformedType::create($class, $name, $type);
    }
}
```

Transformers should be added to the `typescript-transformer` config file:

```php
   ...

    'transformers' => [
        App\Support\TypeScriptTransformer\CustomDateTransformer::class,
        // ...
    ],
    
    ...
```



Running `php artisan typescript:transform` will generate these TypeScript types:

```ts
namespace App.Http.Resources {
    export type Address = {
        street: string | null;
        number: string | null;
        city: string | null;
        postal: string | null;
        country: string | null;
    }

    export type User = {
        age: number | null;
        name: string | null;
        email: string | null;
        address: App.Http.Resources.Address | null;
        birthday: App.Support.CustomDate | null;
    }
}

namespace App.Support {
    export type CustomDate = {
        year: number;
        month: number;
        day: number;
        is_today: boolean;
    }
}
```

If you don't need a separate `App.Support.CustomDate` type, you can choose to inline it in the types where it is used. To do that, use the `createInline` function in the `Transformer`.

```php
class CustomDateTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if(!$class->getName() === CustomDate::class)
        {
            return null;
        }
        
        $type = <<<EOT
export type {$name} = {
    year: number;
    month: number;
    day: number;
    is_today: boolean;
}
EOT;

        return TransformedType::createInline($class, $name, $type);
    }
}
```

When running the `typescript: transform` command, our generated types look like this:

```ts
namespace App.Http.Resources {
    export type Address = {
        street: string | null;
        number: string | null;
        city: string | null;
        postal: string | null;
        country: string | null;
    }

    export type User = {
        age: number | null;
        name: string | null;
        email: string | null;
        address: App.Http.Resources.Address | null;
        birthday: {
            year: number;
            month: number;
            day: number;
            is_today: boolean;
        } | null;
    }
}
```

Instead of using a dedicated `Transformer` as shown above, you can define an inline type right in the `typescript-transformer` config file.


```php
   ...

    'default_type_replacements' => [
        // ...
        CustomDate::class => TypeScriptType::create(<<<EOT
{
    year: number;
    month: number;
    day: number;
    is_today: boolean;
}
EOT
        ),
    ],
    
    ...
```

## Further reading

- [Changing](https://spatie.be/docs/typescript-transformer/v2/usage/annotations) names and transformers in a type's annotation
- [Adding](https://spatie.be/docs/typescript-transformer/v2/dtos/typing-properties) rich types to your DTO's
- [Write](https://spatie.be/docs/typescript-transformer/v2/transformers/type-processors) class property processors that can change types completely
