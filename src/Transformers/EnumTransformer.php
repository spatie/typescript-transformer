<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionEnum;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class EnumTransformer implements Transformer
{
    public function __construct(protected TypeScriptTransformerConfig $config)
    {
    }

    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        // If we're not on PHP >= 8.1, we don't support native enums.
        if (! method_exists($class, 'isEnum')) {
            return null;
        }

        if (! $class->isEnum()) {
            return null;
        }

        return $this->config->shouldTransformToNativeEnums()
            ? $this->toEnum($class, $name)
            : $this->toType($class, $name);
    }

    protected function toEnum(ReflectionClass $class, string $name): TransformedType
    {
        $enum = (new ReflectionEnum($class->getName()));

        $options = array_map(
            fn (ReflectionEnumBackedCase $case) => "'{$case->getName()}' = '{$case->getBackingValue()}'",
            $enum->getCases()
        );

        return TransformedType::create(
            $class,
            $name,
            implode(', ', $options),
            keyword: 'enum'
        );
    }

    protected function toType(ReflectionClass $class, string $name): TransformedType
    {
        $enum = (new ReflectionEnum($class->getName()));

        $options = array_map(
            fn ($enum) => "'{$enum}'",
            array_keys($enum->getConstants())
        );

        return TransformedType::create(
            $class,
            $name,
            implode(' | ', $options)
        );
    }
}
