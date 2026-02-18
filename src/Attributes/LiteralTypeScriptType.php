<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\Reference;
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

    /** @var array<string, string|Reference> */
    private array $references;

    /**
     * @param array<AdditionalImport> $additionalImports
     * @param array<string, string|Reference> $references
     */
    public function __construct(
        string|array $typeScript,
        array $additionalImports = [],
        array $references = [],
    ) {
        $this->typeScript = $typeScript;
        $this->additionalImports = $additionalImports;
        $this->references = $references;
    }

    public function getType(PhpClassNode $class): TypeScriptNode
    {
        if (is_string($this->typeScript)) {
            return new TypeScriptRaw($this->typeScript, $this->additionalImports, $this->references);
        }

        $properties = collect($this->typeScript)
            ->map(fn (string $type, string $name) => new TypeScriptProperty(
                $name,
                new TypeScriptRaw($type, $this->additionalImports, $this->references)
            ))
            ->all();

        return new TypeScriptObject($properties);
    }
}
