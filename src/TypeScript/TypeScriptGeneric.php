<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptGeneric implements TypeScriptNode, TypeScriptVisitableNode, TypeScriptForwardingExportableNode
{
    /**
     * @param  array<TypeScriptNode>  $genericTypes
     */
    public function __construct(
        public TypeScriptIdentifier|TypeReference $type,
        public array $genericTypes,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $generics = implode(', ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->genericTypes
        ));

        return "{$this->type->write($context)}<{$generics}>";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('type')->iterable('genericTypes');
    }

    public function getForwardedExportableNode(): TypeScriptExportableNode|TypeScriptForwardingExportableNode
    {
        return $this->type;
    }
}
