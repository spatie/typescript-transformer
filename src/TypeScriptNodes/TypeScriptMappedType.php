<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptMappedType implements TypeScriptNode
{
    public function __construct(
        public string $typeParameterName,
        #[NodeVisitable]
        public TypeScriptNode $constraint,
        #[NodeVisitable]
        public TypeScriptNode $type,
        #[NodeVisitable]
        public ?TypeScriptNode $nameType = null,
        public ?string $readonlyModifier = null,
        public ?string $optionalModifier = null,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $readonly = $this->readonlyModifier !== null
            ? "{$this->readonlyModifier} "
            : '';

        $optional = $this->optionalModifier ?? '';

        $nameClause = $this->nameType !== null
            ? " as {$this->nameType->write($context)}"
            : '';

        return "{ {$readonly}[{$this->typeParameterName} in {$this->constraint->write($context)}{$nameClause}]{$optional}: {$this->type->write($context)} }";
    }
}
