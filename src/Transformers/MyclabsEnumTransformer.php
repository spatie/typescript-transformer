<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class MyclabsEnumTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if($class->isSubclassOf(Enum::class) === false){
            return null;
        }

        return TransformedType::create(
            $class,
            $name,
            $this->resolveOptions($class)
        );
    }

    protected function resolveOptions(ReflectionClass $class): string
    {
        /** @var \MyCLabs\Enum\Enum $enum */
        $enum = $class->getName();

        $options = array_map(
            fn (Enum $enum) => "'{$enum->getValue()}'",
            $enum::values()
        );

        return implode(' | ', $options);
    }
}
