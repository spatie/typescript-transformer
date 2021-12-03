<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\Enum\Enum;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class SpatieEnumNativeTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if ($class->isSubclassOf(Enum::class) === false) {
            return null;
        }

        return TransformedType::create(
            $class,
            $name,
            $this->resolveOptions($class),
            null,
            false,
            'enum'
        );
    }

    private function resolveOptions(ReflectionClass $class): string
    {
        /** @var \Spatie\Enum\Enum $enum */
        $enum = $class->getName();
    
        $options = array_map(
            fn ($key, $value) => "'{$key}' = '{$value}'",
            array_keys($enum::toArray()), $enum::toArray()
        );
    
        return implode(', ', $options);
    }
}
