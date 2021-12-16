<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\Enum\Enum;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class SpatieEnumTransformer implements Transformer
{
    public function __construct(protected TypeScriptTransformerConfig $config)
    {
    }

    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if ($class->isSubclassOf(Enum::class) === false) {
            return null;
        }

        return $this->config->shouldTransformToNativeEnums()
            ? $this->toEnum($class, $name)
            : $this->toType($class, $name);
    }

    protected function toEnum(ReflectionClass $class, string $name): TransformedType
    {
        /** @var \Spatie\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn ($key, $value) => "'{$key}' = '{$value}'",
            array_keys($enum::toArray()),
            $enum::toArray()
        );

        return TransformedType::create(
            $class,
            $name,
            implode(', ', $options),
            keyword: 'enum'
        );
    }

    private function toType(ReflectionClass $class, string $name): TransformedType
    {
        /** @var \Spatie\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn ($enum) => "'{$enum}'",
            array_keys($enum::toArray())
        );

        return TransformedType::create(
            $class,
            $name,
            implode(' | ', $options)
        );
    }
}
