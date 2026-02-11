<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptAlias implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    #[NodeVisitable]
    public TypeScriptIdentifier|TypeScriptGeneric $identifier;

    public function __construct(
        TypeScriptIdentifier|TypeScriptGeneric|string $identifier,
        #[NodeVisitable]
        public TypeScriptNode $type,
    ) {
        $this->identifier = is_string($identifier)
            ? new TypeScriptIdentifier($identifier)
            : $identifier;
    }

    public function write(WritingContext $context): string
    {
        return "type {$this->identifier->write($context)} = {$this->type->write($context)};";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
