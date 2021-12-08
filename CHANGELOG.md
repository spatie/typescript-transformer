# Changelog

All notable changes to `typescript-transformer` will be documented in this file

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
