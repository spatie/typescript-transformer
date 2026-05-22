---
title: Routes
weight: 4
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

## Handling unknown routes

The generated `route()` helper throws an error when called with a name that does not exist in the manifest. This mirrors Laravel's server-side `route()` behavior (which throws a `RouteNotFoundException`) and surfaces typos at the call site rather than silently producing broken URLs.

```ts
route('does-not-exist');
// throws: Route "does-not-exist" not found.
```

In well-typed TypeScript code this is impossible to hit. The signature constrains `name` to keys of the generated `RouteParameters` type, so the type checker will reject unknown names before the call is ever made.

When you do work with dynamic names (locale-aware routing wrappers, runtime-composed keys), use the `routeExists` predicate to guard the call:

```ts
import {route, routeExists} from './helpers/route';

function safeRoute(name: string) {
    if (routeExists(name)) {
        return route(name);
    }

    return null;
}
```

`routeExists` is a type predicate, so inside the guarded branch TypeScript narrows `name` to `keyof RouteParameters`. That means the subsequent `route(name)` call type-checks even when `name` started as a plain `string`.

You can exclude certain routes from being included in the generated TypeScript using [route filters](/docs/typescript-transformer/v3/laravel/route-filters).

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
change your routes in Laravel. By default, the watcher will monitor the following directories for changes:

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
