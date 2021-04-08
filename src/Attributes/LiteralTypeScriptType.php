<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use phpDocumentor\Reflection\Type;
use Spatie\TypeScriptTransformer\Types\StructType;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;

#[Attribute]
class LiteralTypeScriptType implements TypeScriptTransformableAttribute
{
    private string | array $typeScript;

    public function __construct(string | array $typeScript)
    {
        $this->typeScript = $typeScript;
    }

    public function getType(): Type
    {
        if (is_string($this->typeScript)) {
            return new TypeScriptType($this->typeScript);
        }

        $types = array_map(
            fn (string $type) => new TypeScriptType($type),
            $this->typeScript
        );

        return new StructType($types);
    }
}
