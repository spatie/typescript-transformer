<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Types\StructType;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptRaw;

#[Attribute]
class LiteralTypeScriptType implements TypeScriptTypeAttributeContract
{
    private string|array $typeScript;

    public function __construct(string|array $typeScript)
    {
        $this->typeScript = $typeScript;
    }

    public function getType(ReflectionClass $class): TypeScriptNode
    {
        if (is_string($this->typeScript)) {
            return new TypeScriptRaw($this->typeScript);
        }

        $properties = collect($this->typeScript)
            ->map(fn (string $type, string $name) => new TypeScriptProperty(
                $name,
                new TypeScriptRaw($type)
            ))
            ->all();

        return new TypeScriptObject($properties);
    }
}
