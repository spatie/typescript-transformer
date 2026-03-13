<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;

class TypeScriptNamespace implements TypeScriptNode, TypeScriptNamedNode
{
    /**
     * @param array<TypeScriptNode|Transformed> $types
     * @param array<TypeScriptNamespace|TypeScriptOperator> $children
     */
    public function __construct(
        public string $name,
        #[NodeVisitable]
        public array $types,
        #[NodeVisitable]
        public array $children = [],
    ) {
    }

    public function write(WritingContext $context): string
    {
        $output = "namespace {$this->name} {"."\n";

        $context->pushNamespace($this->name);

        foreach ($this->types as $type) {
            $output .= $type->write($context)."\n";
        }

        foreach ($this->children as $child) {
            $output .= $child->write($context)."\n";
        }

        $context->popNamespace();

        $output .= '}';

        return $output;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
