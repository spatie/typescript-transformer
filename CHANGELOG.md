# Changelog

All notable changes to `typescript-transformer` will be documented in this file

## 2.3.0 - 2024-02-16

### What's Changed

* Fix annotations doc by @cosmastech in https://github.com/spatie/typescript-transformer/pull/73
* Fix backslashes conversion to TypeScript by @Bloemendaal in https://github.com/spatie/typescript-transformer/pull/72
* Add `DtoTransformer@transformPropertyName()` by @cosmastech in https://github.com/spatie/typescript-transformer/pull/74
* Support PHP Parser 5
* Removal of Psalm
* Addition of PHPStan

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.2.2...2.3.0

## 2.2.2 - 2023-12-01

### What's Changed

* Allow Symfony 7 by @jmsche in https://github.com/spatie/typescript-transformer/pull/67

### New Contributors

* @jmsche made their first contribution in https://github.com/spatie/typescript-transformer/pull/67

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.2.1...2.2.2

## 2.2.1 - 2023-07-05

- Add support for pseudo types

## 2.2.0 - 2023-06-02

- Add support for hidden properties (#54)

## 2.1.14 - 2023-04-07

- add support for record types (#51)

## 2.1.13 - 2023-02-01

- Add EnumCollector (#42)
- Ensure transformed types are unique (#44)

## 2.1.12 - 2022-11-18

- add support for optional attributes (#30)
- refactor tests to Pest (#39)

## 2.1.11 - 2022-09-28

- fix: Support Collection with array-key key type (#38)

## 2.1.10 - 2022-07-04

- Allow non fully qualified names within annotations

## 2.1.9 - 2022-06-29

- allow transformation of interfaces (#32)

## 2.1.8 - 2022-04-29

- add eslint formatter(#28)
- let prettier formatter use `npx` (#29)

## 2.1.7 - 2022-04-06

- Allow whitespace in type definitions (#27 )

## 2.1.6 - 2022-01-05

- fix the transformation of PHP native enums

## 2.1.5 - 2021-12-29

## What's Changed

- Make compatible with Symfony 6.0 Process component by @firstred in https://github.com/spatie/typescript-transformer/pull/17

## New Contributors

- @firstred made their first contribution in https://github.com/spatie/typescript-transformer/pull/17

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.1.4...2.1.5

## 2.1.4 - 2021-12-23

- allow interfaces in default type replacements

## 2.1.3 - 2021-12-16

- add support for transforming to native TypeScript enums

## 2.1.2 - 2021-12-16

- fix deprecations

## 2.1.1 - 2021-12-08

- add support for PHP 8.1 (#15)

## 2.1.0 - 2021-04-08

- Remove classtools dependency
- Add support for PHP 8.1 enums (#12)
- Add `declare` keyword by default to generated output (#13)

## 2.0.3 - 2021-07-09

- Fix `ProcessTypes` to work with Collection types

## 2.0.2 - 2021-06-30

- Fix default collector with missing symbols in attributes

## 2.0.1 - 2021-04-14

- Allow spatie/temporary-directory v2 on dev

## 2.0.0 - 2021-04-08

- The package is now PHP 8 only
- Added TypeReflectors to reflect method return types, method parameters & class properties within your transformers
- Added support for attributes
- Added support for manually adding TypeScript to a class or property
- Added formatters like Prettier which can format TypeScript code
- Added support for inlining types directly
- Updated the DtoTransformer to be a lot more flexible for your own projects
- Added support for PHP 8 union types

## 1.1.2 - 2021-01-07

- Add support for `Writers` (#7)

## 1.1.1 - 2020-11-26

- Add PHP8 support

## 1.1.0 - 2020-11-26

- Fix some capitalization in namespace names
- Added `SpatieEnumTransformer` from the `laravel-typescript-transformer` package

## 1.0.0 - 2020-09-02

- initial release
