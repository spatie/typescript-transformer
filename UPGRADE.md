# Upgrading to v2

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
