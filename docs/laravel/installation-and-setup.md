---
title: Installation and setup
weight: 1
---

You can install the package via composer:

```bash
composer require spatie/laravel-typescript-transformer
```

When using Laravel, first install the specific `TypeScriptTransformerServiceProvider`:

```bash
php artisan typescript:install
```

This command will create a `TypeScriptTransformerServiceProvider` in your `app/Providers` directory. Which looks like
this:

```php
class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config; // We'll come back to this in a minute
    }
}
```

And it will also register the service provider in your `bootstrap/providers.php` file (when running Laravel 11 or
above). Or in your `config/app.php` file when running Laravel 10 or below.

Now you can transform types as such:

```bash
php artisan typescript:transform
```

Since we haven't configured TypeScript transformer yet, this command won't do anything.

In order to configure TypeScript Transformer, we recommend you to now continue reading the documentation on the
framework-agnostic [getting started](/docs/typescript-transformer/v3/getting-started/first-run) section. The docs will
explain how to configure the package which is by modifying the `$config` object we saw earlier in the
`TypeScriptTransformerServiceProvider`.

After you're done reading the framework-agnostic docs, you can return here to read about Laravel-specific features this
package provides.

## What the Laravel package provides

Out of the box, the Laravel package automatically configures a few things for you:

- `CarbonInterface` (and `Carbon`) types are replaced with `string` in TypeScript
- The `AttributedClassTransformer` is replaced with `LaravelAttributedClassTransformer` which adds proper handling for
  Laravel's `Collection` and `EloquentCollection` as array-like structures
- TypeScript types are generated for Laravel's pagination classes: `LengthAwarePaginator` and `CursorPaginator`. These
  types include the full pagination structure with `data`, `links` and`meta` properties, so you can use them in your
  frontend code without defining them yourself.

These are all configured through the `LaravelTypeScriptTransformerExtension` which is loaded automatically when using
the [Laravel Data](/docs/typescript-transformer/v3/laravel/laravel-data)
or [controllers](/docs/typescript-transformer/v3/laravel/controllers) extensions. If you're not using either of those
but still want the base Laravel types, you can add the extension manually:

```php
use Spatie\LaravelTypeScriptTransformer\LaravelTypeScriptTransformerExtension;

protected function configure(TypeScriptTransformerConfigFactory $config): void
{
    $config->extension(new LaravelTypeScriptTransformerExtension());
}
```
