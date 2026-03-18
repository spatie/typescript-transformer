# Changelog

All notable changes to `typescript-transformer` will be documented in this file

## 3.1.1 - 2026-03-18

### What's Changed

- Throw exception when output directory does not exist instead of silently resolving to an empty path (#134)

## 3.1.0 - 2026-03-16

### What's Changed

- Support class-level `@template` generics in TypeScript output (#133)
- Remove unused service provider stub

Classes with `@template` docblocks now produce generic type aliases:

```php
/**
 * @template T
 */
class PaginatedResponse
{
    /** @param array<T> $data */
    public function __construct(
        public int $page = 1,
        public array $data = [],
    ) {}
}

```
Now correctly generates:

```ts
type PaginatedResponse<T> = {
    page: number;
    data: T[];
};

```
Instead of the previous incorrect output where `T` was resolved as `unknown`.

## 3.0.0 - 2026-03-13

Version 3 is a ground-up rewrite. It introduces a TypeScript AST, a visitor pattern, watch mode, a new extension system, and much more.

### TypeScript AST

The package now builds a proper TypeScript Abstract Syntax Tree before writing output. Instead of generating strings directly, transformers create node objects that can be traversed and manipulated before being written to disk:

```php
new TypeScriptAlias('User', new TypeScriptObject([
    new TypeScriptProperty('name', new TypeScriptString()),
    new TypeScriptProperty('age', new TypeScriptNumber()),
]));
// Output: type User = { name: string; age: number }


```
There are a lot of node types available and you can easily add your own!

### Visitor Pattern

A `Visitor` allows users to traverse the AST, allowing them to replace or completely remove nodes:

```php
Visitor::create()
    ->after(function (TypeScriptUnion $node) {
        if (count($node->types) === 1) {
            return VisitorOperation::replace(array_values($node->types)[0]);
        }
    })
    ->execute($rootNode);


```
### Watch Mode

A file system watcher monitors your PHP files and automatically re-transforms on changes. Your TypeScript definitions stay in sync as you develop - no manual re-running required.

This feature is in beta at the moment.

### References & Cross-File Linking

`TypeScriptReference` nodes connect generated types to the PHP classes they represent. The system automatically resolves references to the correct import paths based on your writer configuration.

### TransformedProvider

A new provider interface lets you inject custom transformed types from any source - not just PHP classes:

```php
class AddLaravelCollectionProvider implements TransformedProvider
{
    public function provide(): array
    {
        return [new Transformed(
            typeScriptNode: new TypeScriptAlias(
                new TypeScriptGeneric(new TypeScriptIdentifier('Collection'), [new TypeScriptIdentifier('T')]),
                new TypeScriptGeneric(new TypeScriptIdentifier('Array'), [new TypeScriptIdentifier('T')]),
            ),
            reference: new ClassStringReference(Collection::class),
            location: ['Illuminate', 'Support'],
        )];
    }
}
// Output: type Collection<T> = Array<T>


```
### Rewritten Transformer System

Collectors have been removed. Transformers now decide both *whether* they can handle a type and *how* to transform it:

```php
class MyTransformer extends ClassTransformer
{
    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return $phpClassNode->implementsInterface(Data::class);
    }
}


```
### Rewritten Enum Support

The `EnumTransformer` now supports union output, native TypeScript enums, and a pluggable `EnumProvider` interface for custom enum detection.

### PHPStan Type Inference

PHPDocumentor has been replaced by PHPStan's type parser. This provides more robust handling of generics, array shapes, `key-of`, `value-of`, and complex union/intersection types.

### Dual Writer System

`ModuleWriter` generates TypeScript modules in a directory structure mirroring your PHP namespaces. `GlobalNamespaceWriter` outputs a single `.d.ts` declaration file with namespaced types in global scope.

### PhpNode Abstraction

Transformers now work with `PhpClassNode`, `PhpPropertyNode`, `PhpMethodNode` instead of raw PHP Reflection objects, providing a unified interface allowing updates to the files to be handled in the same process.

### Breaking Changes

- Requires PHP 8.2+
- Collectors removed in favor of Transformers
- `DtoTransformer` removed - use `ClassTransformer` with custom property processors
- `TypeProcessors` replaced by `ClassPropertyProcessor`
- TypeReflectors removed
- Inline type support removed
- `RecordTypeScriptType` and `TypeScriptTransformer` attributes removed

Since this is a complete rewrite, there isn't an upgrade guide available. We recommend you to first read [full documentation](https://spatie.be/docs/typescript-transformer/v3/introduction) and then upgrade your projects accordingly.

## 2.5.0 - 2025-04-25

### What's Changed

* Dropped support for PHP 8.0
* Fix: EnumTransformer properly handling single-quotes in backed enum string values by @sugarmaplemedia in https://github.com/spatie/typescript-transformer/pull/100
* fix: No quotes for the enum case according to typescriptlang.org by @ABartelt in https://github.com/spatie/typescript-transformer/pull/97

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.4.0...2.5.0

## 2.4.1 - 2025-04-25

### What's Changed

* Fix: EnumTransformer properly handling single-quotes in backed enum string values by @sugarmaplemedia in https://github.com/spatie/typescript-transformer/pull/100
* fix: No quotes for the enum case according to typescriptlang.org by @ABartelt in https://github.com/spatie/typescript-transformer/pull/97

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.4.0...2.4.1

## 2.4.0 - 2024-10-04

### What's Changed

* Don't generate if an enum has no cases yet by @jameshulse in https://github.com/spatie/typescript-transformer/pull/87
* feat: support `nullToOptional` config by @innocenzi in https://github.com/spatie/typescript-transformer/pull/88

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.3.1...2.4.0

## 2.3.1 - 2024-05-03

### What's Changed

* feat(enum-collector): improve extensibility of `EnumTransformer` by @innocenzi in https://github.com/spatie/typescript-transformer/pull/78

**Full Changelog**: https://github.com/spatie/typescript-transformer/compare/2.3.0...2.3.1

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
