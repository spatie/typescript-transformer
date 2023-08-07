<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptAlias implements TypeScriptNode, TypeScriptVisitableNode, TypeScriptForwardingExportableNode
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

    public function getForwardedExportableNode(): TypeScriptExportableNode|TypeScriptForwardingExportableNode
    {
        return $this->identifier;
    }
}
