<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionEnum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class EnumTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        // If we're not on PHP >= 8.1, we don't support native enums.
        if (! method_exists($class, 'isEnum')) {
            return null;
        }

        if ($class->isEnum() === false) {
            return null;
        }

        return TransformedType::create(
            $class,
            $name,
            $this->resolveOptions($class)
        );
    }

    private function resolveOptions(ReflectionClass $class): string
    {
        $enum = (new ReflectionEnum($class->getName()));

        $options = array_map(
            fn ($enum) => "'{$enum}'",
            array_keys($enum->getConstants())
        );

        return implode(' | ', $options);
    }
}
