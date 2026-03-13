---
title: Route filters
weight: 5
---

Both the [route helper](/docs/typescript-transformer/v3/laravel/routes) and [controller generation](/docs/typescript-transformer/v3/laravel/controllers) support route filters to exclude certain routes from the generated TypeScript output.

Filters are passed to the provider's configuration:

```php
$config->provider(new LaravelRouteTransformedProvider(
    routeFilters: [
        // ...
    ],
));

$config->provider(new LaravelControllerTransformedProvider(
    filters: [
        // ...
    ],
));
```

## NamedRouteFilter

Exclude routes by their name. Wildcards are supported:

```php
use Spatie\LaravelTypeScriptTransformer\RouteFilters\NamedRouteFilter;

new NamedRouteFilter('debugbar.*', 'admin.*'),
```

## ControllerRouteFilter

Exclude routes by their controller class or namespace. Wildcards are supported:

```php
use Spatie\LaravelTypeScriptTransformer\RouteFilters\ControllerRouteFilter;

new ControllerRouteFilter('App\Http\Controllers\Admin\*', 'HiddenController'),
```

You can also filter specific controller actions using a tuple of `[class, method]`:

```php
new ControllerRouteFilter(
    [PostsController::class, 'destroy'],
    [PostsController::class, 'edit'],
)
```

## ClosureRouteFilter

For full control, provide a closure that receives each route. Return `true` to exclude the route:

```php
use Spatie\LaravelTypeScriptTransformer\RouteFilters\ClosureRouteFilter;

new ClosureRouteFilter(function (Route $route) {
    return str_starts_with($route->uri(), 'internal/');
}),
```
