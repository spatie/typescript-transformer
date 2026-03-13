---
title: Controllers
weight: 3
---

TypeScript Transformer can generate typed TypeScript objects for your Laravel controllers. Each controller action becomes
a callable function that knows its URL, HTTP method and route parameters. Response and request types are automatically
extracted from your controller method signatures.

This feature is still quite beta but we found it so cool we already wanted to share it. We would love to hear your feedback and ideas for improvement!

## Setup

Add the `LaravelControllerTransformedProvider` to your `TypeScriptTransformerServiceProvider`:

```php
use Spatie\LaravelTypeScriptTransformer\TransformedProviders\LaravelControllerTransformedProvider;

protected function configure(TypeScriptTransformerConfigFactory $config): void
{
    $config->provider(new LaravelControllerTransformedProvider());
}
```

The next time you run `php artisan typescript:transform`, a TypeScript file will be generated in
the `controllers/` directory containing typed objects for all your controllers.

## How it works

Given a controller like this:

```php
class PostsController
{
    public function index(): LengthAwarePaginator
    {
        // ...
    }

    public function show(string $post): PostData
    {
        // ...
    }

    public function store(PostData $data): PostData
    {
        // ...
    }
}
```

With routes:

```php
Route::get('posts', [PostsController::class, 'index']);
Route::get('posts/{post}', [PostsController::class, 'show']);
Route::post('posts', [PostsController::class, 'store']);
```

The next time you run `php artisan typescript:transform`, a typed TypeScript object will be generated for this controller. Each action becomes a callable function that returns the URL and HTTP method:

```ts
import { PostsController } from './controllers';

const { url, method } = PostsController.index();
// { url: '/posts', method: 'get' }
```

Controllers in nested namespaces like `App\Http\Controllers\Admin\UsersController` are placed in subdirectories:

```ts
import { UsersController } from './controllers/Admin';
```

Actions with route parameters require them as the first argument:

```ts
const { url, method } = PostsController.show({ post: 1 });
// { url: '/posts/1', method: 'get' }
```

You can pass query parameters via the options argument:

```ts
const { url } = PostsController.index({ query: { page: 2, per_page: 15 } });
// '/posts?page=2&per_page=15'
```

When an action is registered for multiple HTTP methods, the default method (first registered) is used. You can access a specific variant directly:

```ts
PostsController.update({ post: 1 })
// { url: '/posts/1', method: 'put' }

PostsController.update.patch({ post: 1 })
// { url: '/posts/1', method: 'patch' }
```

### Request and response types

The generated namespace for each action contains `Request` and `Response` types. You can use these to type your
frontend code:

```ts
import { PostsController } from './controllers';

async function createPost(data: PostsController.store.Request) {
    const { url, method } = PostsController.store();

    const response = await fetch(url, {
        method,
        body: JSON.stringify(data),
    });

    return await response.json() as PostsController.store.Response;
}
```

We'll talk more about how request and response types are determined later in this document.

## Invokable controllers

Invokable controllers (with a single `__invoke` method) are generated as a single callable rather than an object with action methods:

```php
class ShowDashboardController
{
    public function __invoke(): DashboardData
    {
        // ...
    }
}
```

You can call them directly without specifying an action name:

```ts
const { url, method } = ShowDashboardController();
// { url: '/dashboard', method: 'get' }
```

Request and response types are available on the controller namespace:

```ts
const response = await fetch(url);
const data = await response.json() as ShowDashboardController.Response;
```

## Response type resolution

TypeScript Transformer inspects your controller methods to determine response types. The following return types are
recognized:

- **Scalar types**: `string`, `int`, `float`, `bool` and `null`
- **Data objects**: any class implementing `Spatie\LaravelData\Contracts\BaseData`
- **Arrays and shapes**: `array`, `array{name: string, age: int}`
- **Collections**: `Collection<int, PostData>`
- **Data collections**: `DataCollection<int, PostData>`, `PaginatedDataCollection`, `CursorPaginatedDataCollection`
- **Wrapped responses**: `Response<PostData>` or `Inertia\Response<PostData>` (the wrapper is unwrapped)

You can use these types as a PHP return type or in a PHPDoc annotation.

At the moment we're unable to detect what kind of Inertia response will be returned and we do not support Laravel's `Resource` classes, but we plan to add support for these in the future.

When a return type cannot be resolved to a known TypeScript type (e.g. a plain `Response` without a generic), the
response type will be `object`.

### Request type resolution

Request types are detected by looking for a method parameter that is a Data object(from spatie/laravel-data). The first matching parameter
becomes the `Request` type:

```php
public function store(StorePostData $data): PostData
{
    // ...
}
```

If no Data object parameter is found, the request type will be `object`.

In the future we plan to add support for detecting Laravel's `FormRequest` classes as well.

## Action name resolvers

The generated TypeScript file path is derived from the controller's fully qualified class name. For example, `App\Http\Controllers\Posts\PostsController` results in `App/Http/Controllers/Posts/PostsController.ts`.

Since most Laravel controllers live under `App\Http\Controllers`, you'll probably want to strip that prefix. Use the `StrippedActionNameResolver` to do this:

```php
use Spatie\LaravelTypeScriptTransformer\ActionNameResolvers\StrippedActionNameResolver;

$config->provider(new LaravelControllerTransformedProvider(
    actionNameResolver: new StrippedActionNameResolver([
        'App\Http\Controllers' => null,
    ]),
));
```

This turns `App\Http\Controllers\Posts\PostsController` into `Posts/PostsController.ts`. Setting the replacement to `null` strips the prefix entirely. You can also provide a replacement string:

```php
new StrippedActionNameResolver([
    'App\Http\Controllers' => 'controllers',
])
// App\Http\Controllers\Posts\PostsController → controllers/Posts/PostsController
```

### Custom resolver

For full control, use the `ClosureActionNameResolver`. It receives the controller class name and should return an array of path segments:

```php
use Spatie\LaravelTypeScriptTransformer\ActionNameResolvers\ClosureActionNameResolver;

$config->provider(new LaravelControllerTransformedProvider(
    actionNameResolver: new ClosureActionNameResolver(
        fn (string $controllerClass) => ['api', class_basename($controllerClass)]
    ),
));
```

## Route filters

You can exclude certain routes from controller generation using [route filters](/docs/typescript-transformer/v3/laravel/route-filters).

## Output location

By default, controller files are written to a `controllers/` directory inside your configured output directory. You can
change this:

```php
$config->provider(new LaravelControllerTransformedProvider(
    location: 'controllerDefinitions',
));
```

## Watch mode

When running in [watch mode](/docs/typescript-transformer/v3/laravel/watch-mode), the controller provider watches your route directories (`routes/`, `bootstrap/` and `app/Providers/`) for changes to route definitions. You can customize which directories are watched:

```php
$config->provider(new LaravelControllerTransformedProvider(
    routeDirectories: [
        base_path('routes'),
    ],
));
```

If you also want to regenerate controllers when a controller file changes, add the controller directory to your [watch directories](/docs/typescript-transformer/v3/laravel/watch-mode) configuration.
