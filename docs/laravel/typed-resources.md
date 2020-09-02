---
title: A pracical walkthrough
weight: 3
---

This is a more practical look at the package and walks you through a real use case on how we're using this package in our projects.

With this package we at Spatie create fully typed resources, let's take a look on how to accomplish this. We're not going to use the default Laravel resources, because they cannot bee typed. It is for the package impossible to get type definitions from the `toArray` method in a Laravel resource.

Instead we're going to use the Spatie [data-transfer-object](https://github.com/spatie/data-transfer-object) package, this is not required but it makes our life a lot easier. Let's get started! In this example we're going to create a user resource:

```php
class UserResource extends DataTransferObject implements Arrayable
{
    public ?int $age = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?AddressResource $address = null;
}
```

The user also has an address, another model, so we'll add an address resource:

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

Why is each property nullable? When we're creating a new user we want to share some blueprint of the user resource to the frontend. This blueprint is empty, off course! If we would require non-nullable types in our resource, then such blueprint cannot be created. It's free to you to make this properties nullable or not but we think it's kinda neat you can create a blueprint with these reesources.

Normally in your default Laravel resource you would have a `toArray` method, that method would transform a model into a resource. In this example we're doing it a bit differently. We add a static constructor for the resource and will create the resources through that constructor

Our UserResource will now look like this:

```php
class UserResource extends DataTransferObject implements Arrayable
{
    public ?int $age = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?AddressResource $address = null;

    public static function make(User $user): self
    {
        return new self([
            'age' => $user->age,
            'name' => "{$user->first_name} {$user->last_name}",
            'email' => $user->email,
            'address' => AddressResource::make($user->address ?? new Address()),
        ]);
    }
}
```

As you can see when an empty user is given or a user has no address, then an empty Address model is given to the AddressResource. This will create a blueprint for the address as described above. The address resource now looks like this:

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

Using dto's to communicate data with the front has two extra benefits the typing of your data to the frontend will always be correct. In this example we've typed age as an `integer`. When we would make the mistake of providing an age as a `string` to a default Laravel resource nothing would happen. But maybe our frontend would crash due to the fact that it expects that age is an `integer`.

When using dto's it would be impossible to assign a `string` to an `int` type. An eexception would pop up and your application also would stop working but, you know exactly what's going wrong in contrast to finding a type error in your frontend code which can be quite difficult.

A second benefit is ide completion, you can now construct your resource with all the information hinted by your ide. To be fair, this is also possible with Laravel's default resources but requires a `@mixin` annotation.

## Using resources in your project

Now we've got our `UserResource`, let's create a simplified controller:

```php
class UserController
{
    public function create()
    {
        return Inertia::render('users.create', [
            'user' => UserResource::make(new User())
        ]);
    }

    public function update(User $user)
    {
        return Inertia::render('users.update', [
            'user' => UserResource::make($user)
        ]);
    }
}
```

The UserResource is two times used: 

- for creating a blueprint user when you create a new user
- for creating a user object when you're editing a user

As you can see, we're using [Inertia](https://inertiajs.com/responses) here which totally benefits of using this package to create TypeScripted resources. Now we've done our setup, let's start using the package.

## Using the package

We add a `@typescript` annotation to the `UserResource` and `AddressResource` and run following command:

```bash
php artisan typescript:transform
```

We get the following output:

```bash
+------------------------------------+------------------------------------+
| PHP class                          | TypeScript entity                  |
+------------------------------------+------------------------------------+
| App\Http\Resources\UserResource    | App.Http.Resources.UserResource    |
| App\Http\Resources\AddressResource | App.Http.Resources.AddressResource |
+------------------------------------+------------------------------------+
Transformed 2 PHP types to TypeScript

```

A new file was created in the resources directory of our application. `generated.ts` contains two types:

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

Cool! We can now use these types in our TypeScript code, referencing a `UserResource` can now be done using `App.Http.Resource.UserResource`. 

Now, our resources will always be send to the front, they aren't internal structures that should remain in the backend. So it feels a bit tedious to always add an `@typescript` annotation, let's fix that! We're going to create a [collector](https://spatie.be/docs/typescript-transformer/v1/usage/collectors) for our resources that will take all the resource classes and transformed them to TypeScript. Even the ones without the `@typescript` annotation.

First, we need to make it clear for the collector that a class is indeed a resource, so let's create an abstract class Resource:

```php
abstract class Resource extends DataTransferObject implements Arrayable
{
}
```

We also update the class definitions of the `UserResource`:

```php
// From:
class AddressResource extends DataTransferObject implements Arrayable
// To:
class AddressResource extends Resource
```

And the `AddressResource`

```php
// From:
class UserResource extends DataTransferObject implements Arrayable
// To:
class UserResource extends Resource
```

Let's create a collector! This collector will take classes that extend `Resource` and they will be transformed using a `DtoTransformer`. I don't like that our TypeScript types always have Resource prepended so let's also remove that part from the name:

```php
class ResourceCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Resource::class);
    }

    public function getCollectedOccurrence(ReflectionClass $class): CollectedOccurrence
    {
        return CollectedOccurrence::create(
            new DtoTransformer($this->config),
            Str::before($class->getShortName(), 'Resource')
        );
    }
}
```

We return a CollectedOccurrence that's an object with a transformer, in this case a `DtoTransformer` we constructed with the package configuration. And a name which we get from the reflection class and using the Laravel string helpers we remove the Resource part.

Only thing we have to do is adding this collector to the list of collectors in the configuration file `typescript-transformer.php`:

```php
	...

    /*
    |--------------------------------------------------------------------------
    | Collectors
    |--------------------------------------------------------------------------
    |
    | In these classes you define which classes will be collected and fed to
    | transformers. By default, we include an AnnotationCollector which will
    | search for @typescript annotated classes to transform.
    |
    */

    'collectors' => [
        Spatie\TypeScriptTransformer\Collectors\AnnotationCollector::class,
        App\Support\TypeScriptTransformer\ ResourceCollector::class,
    ],
    
    ...
```

When we now remove the `@typescript` annotation from the resources and run the command again:

```bash
+------------------------------------+----------------------------+
| PHP class                          | TypeScript entity          |
+------------------------------------+----------------------------+
| App\Http\Resources\UserResource    | App.Http.Resources.User    |
| App\Http\Resources\AddressResource | App.Http.Resources.Address |
+------------------------------------+----------------------------+
Transformed 2 PHP types to TypeScript
```

We now have to TypeScript types: `User` and `Address`, exactly what we wanted!

### Using class property replacements

Let's take it a bit further and add a birthday to the User. We could just use a `Carbon` instance like so:

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

Since `Carbon` isn't a primitive type like a `string`, `int`, `bool`, `array` we actually cannot send it to the front. But that's easily solved by converting that `Carbon` instance to a `string`. We can do this conversion in the `Resource` class:

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

But what about our TypeScript Type? The package doesn't not know anything about a Carbon type, so it will transform it to `any`. This will work but I think we can do a bit better. 

We've added the class property replacements feature to the package so you can replace common types like `Carbon` with primitive types like a `string`. If you take a look at the `typescript-transformer.php` config file you can see we already added some class property replacements including `Carbon`:

```
	...

    /*
    |--------------------------------------------------------------------------
    | Class property replacements
    |--------------------------------------------------------------------------
    |
    | In your DTO's you sometimes have properties that should always be replaced
    | by TypeScript representations. For example, you can replace a Datetime
    | always with a string. These replacements can be defined here.
    |
    */

    'class_property_replacements' => [
        DateTime::class => 'string',
        DateTimeImmutable::class => 'string',
        Carbon::class => 'string',
        CarbonImmutable::class => 'string',
    ],
    
    ...
```

So the Carbon type in our previous will not transform to `any` but to `string` which is exactly what we wanted! Off course you're free to add as many replacements as you wish.

### Adding a custom transformer

I'm not that big of a fan of Carbon (actually I'm but let's forget that to make this walkthrough a bit easier to follow). So let's not use a `Carbon` object for representing the birthday and create a custom made implementation:

```php
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
            'is_today' => date('Y/n/j') === "{$this->year}/{$this->month}/{$this->day}"
        ];
    }
}
```

We update the `UserResource` with the new `CustomDate` type

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

And the `Resource` so the `get` method `CustomDate` will be called when converting the data for the frontend:

```php
abstract class Resource extends DataTransferObject implements Arrayable
{
    public function toArray(): array
    {
        return collect(parent::toArray())->map(function ($value) {
            if ($value instanceof CustomDate) {
                return $value->get();
            }

            return $value;
        })->toArray();
    }
}
```

Now when running the TypeScript transformer command we get the following:

```ts
...

export type User = {
    age: number | null;
    name: string | null;
    email: string | null;
    address: App.Http.Resources.Address | null;
    birthday: any | null;
}

...
```

That `any` does not describe our home-made `CustomDate` type let's try to fix that. We could create a new transformer for the `Resource` class. In such transformer you could change how a `Resource` will be transformed and which property processors will run.

Property processors will run over each property of your resource and will try to replace complicated types with a more primitive type. For example in the example above where we used a `Carbon` type for the birthdate. This `Carbon` type would be automatically transformed to a `string`. This is done by the `ReplaceDefaultTypesClassPropertyProcessor`.

We now have two options:

- Create a transformer that will transform `CustomDate` into a valid TypeScript type
- Create a new class property processor that replaces `CustomDate` with an array in each property and a new transformer for the resources that uses this class property processor

In this walkthrough we will create a new transformer for `CustomDate` since class property processors are a bit too advanced for this walkthrough. If you would like to make this a bit more challaging, you can always read more about them [here](https://spatie.be/docs/typescript-transformer/v1/dtos/changing-types-with-class-property-processors). 

We will continue with creating a transformer, which has one disadvantage, we have to add a `@typescript` annotation to the class. We could write another collector which automatically collects these classes but that would take us a bit too far.

First we create a transformer, this looks a bit like a collector. First it checks if it can transform a class and then it will transform that class. In contrast to a collector we do not return a `CollectedOccurrence` but a transformed TypeScript type:

```php
class CustomDateTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->getName() === CustomDate::class;
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
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

We're returning a `TransformedType`. This consists of the `ReflectionClass` created from `CustomDate`, a name for the type and the transformed type itself.

When you add this transformer to your `typescript-transformer.php` config:

```php
	...
	
    /*
    |--------------------------------------------------------------------------
    | Transformers
    |--------------------------------------------------------------------------
    |
    | In these classes, you transform your PHP classes(e.g., enums) to
    | their TypeScript counterparts.
    |
    */

    'transformers' => [
        App\Support\TypeScriptTransformer\CustomDateTransformer::class,
        Spatie\LaravelTypeScriptTransformer\Transformers\SpatieEnumTransformer::class,
        Spatie\LaravelTypeScriptTransformer\Transformers\SpatieStateTransformer::class,
        Spatie\TypeScriptTransformer\Transformers\DtoTransformer::class,
    ],
    
    ...
```

Add a `@typescript` to the `CustomDate` class and run the `typescript:transform` command:

```bash
+------------------------------------+----------------------------+
| PHP class                          | TypeScript entity          |
+------------------------------------+----------------------------+
| App\Support\CustomDate             | App.Support.CustomDate     |
| App\Http\Resources\UserResource    | App.Http.Resources.User    |
| App\Http\Resources\AddressResource | App.Http.Resources.Address |
+------------------------------------+----------------------------+
Transformed 3 PHP types to TypeScript
```

Our generated Typescript now looks like this:

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

That looks great! But I think we could even take it a bit further, why should we reference the `CustomDate` type as `App.Support.CustomDate`, if it is actually a simple object? We can use inline types to solve this! Let's change our transformer to use an inline type:

```php
class CustomDateTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->getName() === CustomDate::class;
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        $type = <<<EOT
{
    year: number;
    month: number;
    day: number;
    is_today: boolean;
}
EOT;

        return TransformedType::createInline($class, $type);
    }
}
```

An inline type has no name since it will be inlined directly with other types, also notice we removed the `export type CustomDate =` part of the transformed type. Now when running the `typescript:transform` command our generated types looks like this:

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

Nice! But you can already hear me comming, we can do this just a bit better. Transformers normally transform classes that are abstract. To be more clear classes you use as a blueprint for creating other classes. For exmple you wouldn't create a transformer for each enum you create. No, you would create a transformer for the base `Enum` class, each child class of `Enum` will be transformed with the same transformer since the transformation process is the same for each type of enum.

So actually creating a transformer for one type, in our case the `CustomDate` type is maybe a bit too much. You can also use a class property replacement. In previous sections we said you can only replace types by their primitive version. But it is actually possible to write TypeScript directly.

In your `typescript-transformer.php` config:

```php
	...

    /*
    |--------------------------------------------------------------------------
    | Class property replacements
    |--------------------------------------------------------------------------
    |
    | In your DTO's you sometimes have properties that should always be replaced
    | by TypeScript representations. For example, you can replace a Datetime
    | always with a string. These replacements can be defined here.
    |
    */

    'class_property_replacements' => [
        DateTime::class => 'string',
        DateTimeImmutable::class => 'string',
        Carbon::class => 'string',
        CarbonImmutable::class => 'string',
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

We can now remove the `CustomDateTransformer` and remove the `@typescript` annotation from `CustomDate`. When you run the `typescript:transform` command, you will notice that the generated types file looks exactly the same!

## In the end

Although this was a short introduction, we already covered a lot of the package, but there's more things you can accomplish:

- [Changing](https://spatie.be/docs/typescript-transformer/v1/usage/annotations) names and transformers in a type's annotation
- [Adding](https://spatie.be/docs/typescript-transformer/v1/dtos/typing-properties) rich types to your dto's
- [Write](https://spatie.be/docs/typescript-transformer/v1/dtos/changing-types-with-class-property-processors) class property processors that can change types completely

