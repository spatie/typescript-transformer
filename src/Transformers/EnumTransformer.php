<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumUnitCase;
use ReflectionEnumBackedCase;
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

        $enum = (new ReflectionEnum($class->getName()));

        if (! $enum->isBacked()) {
            return null;
        }

        return $this->config->shouldTransformToNativeEnums()
            ? $this->toEnum($enum, $name)
            : $this->toType($enum, $name);
    }

    protected function toEnum(ReflectionEnum $enum, string $name): TransformedType
    {
        $options = array_map(
            fn (ReflectionEnumBackedCase $case) => "'{$case->getName()}' = '{$case->getBackingValue()}'",
            $enum->getCases()
        );

        return TransformedType::create(
            $enum,
            $name,
            implode(', ', $options),
            keyword: 'enum'
        );
    }

    protected function toType(ReflectionEnum $enum, string $name): TransformedType
    {
        $options = array_map(
            fn (ReflectionEnumBackedCase $case) => "'{$case->getBackingValue()}'",
            $enum->getCases(),
        );

        return TransformedType::create(
            $enum,
            $name,
            implode(' | ', $options)
        );
    }
}
