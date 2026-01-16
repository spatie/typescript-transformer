<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;

#[Attribute]
class LiteralTypeScriptType implements TypeScriptTypeAttributeContract
{
    private string|array $typeScript;

    public function __construct(string|array $typeScript)
    {
        $this->typeScript = $typeScript;
    }

    public function getType(PhpClassNode $class): TypeScriptNode
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
