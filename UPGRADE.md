# Upgrading to v2

- The Laravel package now needs an array of searching paths instead of a string
- In the Laravel config `searching_path` is renamed to `searching_paths`
- default_type_replacements renamed
- property processors renamed
- Removal of collected occurrence
- Collectors now return transformed types


Nieuw:
- Typereflectors
- Attributes for custom typescript (both on classes and methods, propeties in classes)
- Add support for formatters
- Attributes for parsing classes
- PHP 8 union types

Voor relase -> laravel-typescript transforer (dependencies naar local weg halen)
