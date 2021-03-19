# Upgrading to v2

- The `ClassPropertyProcessor` interface was renamed to `TypeProcessor` and now takes a union of reflection objects
- In the config `classPropertyReplacements` was renamed to `defaultTypeReplacements`
- In the DTO transformer `getClassPropertyProcessors` was renamed to `typeProcessors`
- Collectors now should return a `TransformedType` instead of a `CollectedOccurrence`
- The DTO transformer was completely rewritten, please take a look at the docs how to create you own

Laravel
- In the Laravel config:
    - `searching_path` is renamed to `searching_paths`
    -  `class_property_replacements` is renamed to `default_type_relacements`
    -  `writer` and `formatter` were added 
- You should add the `AttributeCollector::class` to your `collectors` key in the config file when you want to use attributes
- It is not possible anymore to convert one file to TypeScript
