<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;

#[Attribute]
class TypeScriptType implements TypeScriptTypeAttributeContract
{
    private array|string $type;

    public function __construct(string|array $type)
    {
        $this->type = $type;
    }

    public function getType(PhpClassNode $class): TypeScriptNode
    {
        $docResolver = new DocTypeResolver();
        $transpiler = new TranspilePhpStanTypeToTypeScriptNodeAction();

        if (is_string($this->type)) {
            return $transpiler->execute($docResolver->type($this->type), $class);
        }

        $properties = collect($this->type)
            ->map(fn (string $type, string $name) => new TypeScriptProperty(
                $name,
                $transpiler->execute($docResolver->type($type), $class)
            ))
            ->all();

        return new TypeScriptObject($properties);
    }
}
