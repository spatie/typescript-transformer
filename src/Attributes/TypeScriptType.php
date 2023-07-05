<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptTypeAction;
use Spatie\TypeScriptTransformer\Exceptions\UnableToTransformUsingAttribute;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\Types\StructType;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

#[Attribute]
class TypeScriptType implements TypeScriptTypeAttributeContract
{
    private array|string $type;

    public function __construct(string|array $type)
    {
        $this->type = $type;
    }

    public function getType(ReflectionClass $class): TypeScriptNode
    {
        $docResolver = new DocTypeResolver();
        $transpiler = new TranspilePhpStanTypeToTypeScriptTypeAction();

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
