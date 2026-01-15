<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptAlias implements TypeScriptForwardingNamedNode, TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public TypeScriptIdentifier|TypeScriptGeneric $identifier,
        public TypeScriptNode $type,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return "type {$this->identifier->write($context)} = {$this->type->write($context)};";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('identifier', 'type');
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->identifier;
    }
}
