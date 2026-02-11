<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;

class TypeScriptVariableDeclaration implements TypeScriptForwardingNamedNode, TypeScriptNode
{
    #[NodeVisitable]
    public TypeScriptIdentifier $name;

    public function __construct(
        public string $kind,
        TypeScriptIdentifier|string $name,
        #[NodeVisitable]
        public TypeScriptNode $initializer,
        #[NodeVisitable]
        public ?TypeScriptNode $type = null,
    ) {
        $this->name = is_string($name) ? new TypeScriptIdentifier($name) : $name;
    }

    public static function const(
        TypeScriptIdentifier|string $name,
        TypeScriptNode $initializer,
        ?TypeScriptNode $type = null,
    ): self {
        return new self('const', $name, $initializer, $type);
    }

    public static function let(
        TypeScriptIdentifier|string $name,
        TypeScriptNode $initializer,
        ?TypeScriptNode $type = null,
    ): self {
        return new self('let', $name, $initializer, $type);
    }

    public static function var(
        TypeScriptIdentifier|string $name,
        TypeScriptNode $initializer,
        ?TypeScriptNode $type = null,
    ): self {
        return new self('var', $name, $initializer, $type);
    }

    public function write(WritingContext $context): string
    {
        $typeAnnotation = $this->type !== null
            ? ": {$this->type->write($context)}"
            : '';

        return "{$this->kind} {$this->name->write($context)}{$typeAnnotation} = {$this->initializer->write($context)}";
    }

    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode
    {
        return $this->name;
    }
}
