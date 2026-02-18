---
title: Enum transformer
weight: 3
---

The package ships with a built-in EnumTransformer which can transform PHP enums to TypeScript enums or union types.

It is possible to configure this transformer to generate enums for other types of enums than the native PHP enums by
implementing a custom `EnumProvider`:

```php
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\EnumProvider;

use Spatie\Enum\Enum;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class SpatieEnumProvider implements EnumProvider
{
    public function isEnum(PhpClassNode $phpClassNode): bool
    {
        return $phpClassNode->reflection->isSubclassOf(Enum::class);
    }

    public function isValidUnion(PhpClassNode $phpClassNode): bool
    {
        return true;
    }

    public function resolveCases(PhpClassNode $phpClassNode): array
    {
        /** @var class-string<Enum> $className */
        $className = $phpClassNode->getName();

        return array_map(
            fn (Enum $enum) => [
                'name' => $enum->value,
                'value' => $enum->value,
            ],
            $className::cases()
        );
    }
}
```

The `isEnum` method should return whether the provided class node is an enum, the `isValidUnion` method should return
whether the enum can be transformed to a union type rather than a TypeScript enum. The `resolveCases` method should
return an array of cases for the enum which is a mapping of case names to their values.

Within your configuration you can register the transformer as such:

```php
$config->transformer(
    new EnumTransformer(enumProvider: new SpatieEnumProvider())
);
```
