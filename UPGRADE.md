# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not
cover. We accept PRs to improve this guide.

## Upgrading to v3

Version 3 is a complete rewrite of the package. That's why writing an upgrade guide is not that easy. The best way to 
upgrade is to start reading the new docs and try to implement the new features.

A few noticeable changes are:

- Laravel installs now need to configure the package in a service provider instead of config file
- The package requires PHP 8.2
- If you're using Laravel, v10 is minimally required
- Collectors were removed in favor of Transformers which decide whether a type should be transformed or not
- The transformer should now return a `Transformed` object when it can transform a type
- The transformer interface now should return `Untransformable` when it cannot transform the type
- The `DtoTransformer` was removed in favor of a more flexible transformer system where you can create your own transformers
- The `EnumTransformer` was rewritten to allow multiple types of enums to be transformed and multiple output structures
- All other enum transformers were removed
- The concept of `TypeProcessors` was removed, `ClassPropertyProcessor` is a kinda replacement for this
- The TypeReflectors were removed
- Support for inline types was removed
- If you were implementing your own attributes, you should now implement the `TypeScriptTypeAttributeContract` interface instead of `TypeScriptTransformableAttribute`
- The `RecordTypeScriptType` attribute was removed since deduction of these kinds of types is now done by the transformer
- The `TypeScriptTransformer` attribute was removed
- If you were implementing your own `Formatter`, please update the `format` method to now work on an array of files
- Instead of Reflection objects being passed around now `Php*Node` objects should be used to infer
- The PHPDocumentor was replaced by the PHPStan type inferrer
- A watch mode was added which means your transformers might be running multiple times in the same process
- Writers were changed to support linking between multiple files and writers

And so much more. We suggest you completely reread the docs and try to implement the new package instead of trying to
upgrade your existing implementation.

## Upgrading to v2

- The package is now PHP 8 only
- The `ClassPropertyProcessor` interface was renamed to `TypeProcessor` and now takes a union of reflection objects
- In the config:
    - `searchingPath` was renamed to `autoDiscoverTypes`
    - `classPropertyReplacements` was renamed to `defaultTypeReplacements`
- Collectors now only have one method: `getTransformedType` which should
    - return `null` when the collector cannot find a transformer
    - return a `TransformedType` from a suitable transformer
- Transformers now only have one method: `transform` which should
    - return `null` when the transformer cannot transform the class
    - return a `TransformedType` if it can transform the class
- In Writers the `replaceMissingSymbols` method was removed and a `replacesSymbolsWithFullyQualifiedIdentifiers` with `bool` as return type was added
- The DTO transformer was completely rewritten, please take a look at the docs how to create you own
- The step classes are now renamed to actions

Laravel
- In the Laravel config:
    - `searching_path` is renamed to `auto_discover_types`
    -  `class_property_replacements` is renamed to `default_type_relacements`
    -  `writer` and `formatter` were added
- You should replace the `DefaultCollector::class` with the `DefaultCollector::class`
- It is not possible anymore to convert one file to TypeScript via command
