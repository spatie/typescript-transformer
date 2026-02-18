---
title: Routes
weight: 3
---

Laravel provides a great way to define routes and then generate URLs to those routes in PHP using the `route()` helper.
While this all works in PHP, it can be a bit of a pain to do the same in TypeScript. TypeScript transformer can help you
here by providing exact copy of the `route()` helper in TypeScript.

To add the helper, add the following provider to your `TypeScriptTransformerServiceProvider`:

```php
use Spatie\LaravelTypeScriptTransformer\TransformedProviders\LaravelRouteTransformedProvider;

protected function configure(TypeScriptTransformerConfigFactory $config): void
{
    $config->provider(new LaravelRouteTransformedProvider());
}
```

The next time you run the `typescript:transform` command, a TypeScript function called `route` will be generated in
the `helpers/route.ts` file.

You can now use the `route` function in your TypeScript code like this:

```ts
import {route} from './helpers/route';

// Without parameters
const indexUrl = route('users.index');
// https://laravel.dev/users

// With parameters
const userUrl = route('users.show', {user: 1});
// https://laravel.dev/users/1
```

TypeScript will be smart enough to provide you autocompletion on these controllers and their parameters.

Sometimes you might want to exclude certain routes from being included in the generated TypeScript. You can do this by
adding a route filter. The package provides three types of route filters:

**NamedRouteFilter**

Allows you to remove routes by their name. It is possible to use wildcards.

```php
use Spatie\LaravelTypeScriptTransformer\RouteFilters\NamedRouteFilter;

$config->provider(new LaravelRouteTransformedProvider(
    routeFilters: [
        new NamedRouteFilter('debugbar.*', 'hidden'),
    ],
));
```

**ControllerRouteFilter**

Allows you to remove routes by their controller class or namespace using wildcards.

```php
use Spatie\LaravelTypeScriptTransformer\RouteFilters\ControllerRouteFilter;

$config->provider(new LaravelRouteTransformedProvider(
    routeFilters: [
        new ControllerRouteFilter(['App\Http\Controllers\Admin\*', 'HiddenController']),
    ],
));
```

**ClosureRouteFilter**

Allows you to provide a closure that will be called for each route. If the closure returns `true`, the route will be
excluded.

```php
use Spatie\LaravelTypeScriptTransformer\RouteFilters\ClosureRouteFilter;

$config->provider(new LaravelRouteTransformedProvider(
    routeFilters: [
        new ClosureRouteFilter(function (Route $route) {
            return str_starts_with($route->uri(), 'internal/');
        }),
    ],
));
```

By default, the helper will generate absolute URLs meaning it includes the app URL. This URL will be fetched from the
window object in JavaScript. If you want to generate relative URLs instead, you can pass `false` as the third parameter,
indicating you don't want absolute URLs:

```ts
const indexUrl = route('users.index', {}, false);
// /users
```

The default value of the absolute parameter can be changed by setting a default for the provider:

```php
$config->provider(new LaravelRouteTransformedProvider(
    absoluteUrlsByDefault: false,
));
```

Now when using the `route` helper in TypeScript, URLs will be relative by default:

```ts
const indexUrl = route('users.index');
// /users
```

TypeScript transformer will automatically generate the `helpers/route.ts` file in the output directory you configured
for TypeScript transformer. It is possible to change the path of this file as such:

```php
$config->provider(new LaravelRouteTransformedProvider(
    path: 'route.ts',
));
```

When running in the watch mode of the package, the generated `route.ts` file will automatically be updated when you
change your routes in Laravel. By default the watcher will monitor the following directories for changes:

- `routes`
- `bootstrap`
- `app/Providers`

It is possible to customize the directories that are monitored as such:

```php
$config->provider(new LaravelRouteTransformedProvider(
    routeDirectories: [
        'custom/routes/directory',
        'another/directory/to/watch',
    ],
));
```
