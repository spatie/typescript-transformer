<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptParameter implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public string $name,
        public TypeScriptNode $type,
        public bool $isOptional = false,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $name = ! preg_match('/^[$_a-zA-Z][$_a-zA-Z0-9]*$/', $this->name)
            ? "'{$this->name}'"
            : $this->name;

        return $this->isOptional
            ? "{$name}?: {$this->type->write($context)}"
            : "{$name}: {$this->type->write($context)}";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('type');
    }
}
