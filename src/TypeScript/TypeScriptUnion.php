<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Closure;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptUnion implements TypeScriptNode, TypeScriptNodeWithChildren
{
    /**
     * @param array<TypeScriptNode> $types
     */
    public function __construct(
        public array $types,
    ) {
    }

    public function write(WritingContext $context): string
    {
        return implode(' | ', array_map(
            fn (TypeScriptNode $type) => $type->write($context),
            $this->types
        ));
    }

    public function contains(Closure $closure): bool
    {
        foreach ($this->types as $type) {
            if ($closure($type)) {
                return true;
            }
        }

        return false;
    }

    public function children(): array
    {
        return $this->types;
    }
}
