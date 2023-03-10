<?php

namespace Spatie\TypeScriptTransformer\Attributes;

use Attribute;
use phpDocumentor\Reflection\Type;
use Spatie\TypeScriptTransformer\Types\RecordType;

#[Attribute]
class RecordTypeScriptType implements TypeScriptTransformableAttribute
{
    private string $keyType;
    private string | array $valueType;
    private bool $array;

    public function __construct(string $keyType, string | array $valueType, ?bool $array = false)
    {
        $this->keyType = $keyType;
        $this->valueType = $valueType;
        $this->array = $array;
    }

    public function getType(): Type
    {
        return new RecordType($this->keyType, $this->valueType, $this->array);
    }
}
