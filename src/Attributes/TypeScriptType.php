<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Exceptions\UnableToTransformUsingAttribute;
use Spatie\TypeScriptTransformer\Types\StructType;

#[Attribute]
class TypeScriptType implements TypeScriptTransformableAttribute
{
    private array | string $type;

    public function __construct(string | array $type)
    {
        $this->type = $type;
    }

    public function getType(): Type
    {
        if (is_string($this->type)) {
            return (new TypeResolver())->resolve($this->type);
        }

        /** @psalm-suppress RedundantCondition  */
        if (is_array($this->type)) {
            return StructType::fromArray($this->type);
        }

        throw UnableToTransformUsingAttribute::create($this->type);
    }
}
