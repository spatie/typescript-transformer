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

    /** @var array<AdditionalImport> */
    private array $additionalImports;

    /** @param array<AdditionalImport> $additionalImports */
    public function __construct(
        string|array $typeScript,
        array $additionalImports = [],
    ) {
        $this->typeScript = $typeScript;
        $this->additionalImports = $additionalImports;
    }

    public function getType(PhpClassNode $class): TypeScriptNode
    {
        if (is_string($this->typeScript)) {
            return new TypeScriptRaw($this->typeScript, $this->additionalImports);
        }

        $properties = collect($this->typeScript)
            ->map(fn (string $type, string $name) => new TypeScriptProperty(
                $name,
                new TypeScriptRaw($type, $this->additionalImports)
            ))
            ->all();

        return new TypeScriptObject($properties);
    }
}
