
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Transform PHP types to TypeScript

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)
[![Tests](https://github.com/spatie/typescript-transformer/workflows/run-tests/badge.svg)](https://github.com/spatie/typescript-transformer/actions?query=workflow%3Arun-tests)
[![Styling](https://github.com/spatie/typescript-transformer/workflows/Check%20&%20fix%20styling/badge.svg)](https://github.com/spatie/typescript-transformer/actions?query=workflow%3A%22Check+%26+fix+styling%22)
[![Psalm](https://github.com/spatie/typescript-transformer/workflows/Psalm/badge.svg)](https://github.com/spatie/typescript-transformer/actions?query=workflow%3APsalm)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/typescript-transformer.svg?style=flat-square)](https://packagist.org/packages/spatie/typescript-transformer)

This package allows you to convert PHP classes to TypeScript. 

This class...

```php
/** @typescript */
class User
{
    public int $id;
    public string $name;
    public ?string $address;
}
```

... will be converted to this TypeScript type:

```ts
export type User = {
    id: number;
    name: string;
    address: string | null;
}
```

Here's another example.

```php
class Languages extends Enum
{
    const TYPESCRIPT = 'typescript';
    const PHP = 'php';
}
```

The `Languages` enum will be converted to:

```tsx
export type Languages = 'typescript' | 'php';
```

You can find the full documentation [here](https://docs.spatie.be/typescript-transformer/v2/introduction/).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/typescript-transformer.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/typescript-transformer)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install this package via composer:

```bash
composer require spatie/typescript-transformer
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
