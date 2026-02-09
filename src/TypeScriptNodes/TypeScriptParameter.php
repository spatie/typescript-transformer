<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptParameter implements TypeScriptNode
{
    public function __construct(
        public string $name,
        #[NodeVisitable]
        public TypeScriptNode $type,
        public bool $isOptional = false,
        #[NodeVisitable]
        public ?TypeScriptNode $defaultValue = null,
        public bool $isSpread = false,
    ) {
    }

    public function write(WritingContext $context): string
    {
        $name = ! preg_match('/^[$_a-zA-Z][$_a-zA-Z0-9]*$/', $this->name)
            ? "'{$this->name}'"
            : $this->name;

        $spread = $this->isSpread ? '...' : '';
        $optional = $this->isOptional ? '?' : '';
        $default = $this->defaultValue !== null
            ? " = {$this->defaultValue->write($context)}"
            : '';

        return "{$spread}{$name}{$optional}: {$this->type->write($context)}{$default}";
    }
}
