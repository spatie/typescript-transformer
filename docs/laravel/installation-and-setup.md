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
