<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Compactors\ConfigCompactor;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TranspilationResult;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class MyclabsEnumTransformer implements Transformer
{
    protected ConfigCompactor $compactor;

    public function __construct(protected TypeScriptTransformerConfig $config)
    {
        $this->compactor = new ConfigCompactor($config);
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
        /** @var \MyCLabs\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn ($key, $value) => "'{$key}' = '{$value}'",
            array_keys($enum::toArray()),
            $enum::toArray()
        );

        return TransformedType::create(
            $class,
            $name,
            TranspilationResult::noDeps(
                implode(', ', $options),
            ),
            $this->compactor,
            keyword: 'enum'
        );
    }

    protected function toType(ReflectionClass $class, string $name): TransformedType
    {
        /** @var \MyCLabs\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn (Enum $enum) => "'{$enum->getValue()}'",
            $enum::values()
        );

        return TransformedType::create(
            $class,
            $name,
            TranspilationResult::noDeps(
                implode(' | ', $options)
            ),
            $this->compactor
        );
    }
}
