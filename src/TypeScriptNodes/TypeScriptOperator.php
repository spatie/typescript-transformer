<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;
use Spatie\TypeScriptTransformer\Support\WritingContext;

class TypeScriptOperator implements TypeScriptNode, TypeScriptVisitableNode
{
    public function __construct(
        public string $operator,
        public TypeScriptNode $right,
        public ?TypeScriptNode $left = null,
    ) {
    }

    public static function in(
        TypeScriptNode $needle,
        TypeScriptNode $haystack,
    ): self {
        return new self('in', $haystack, $needle);
    }

    public static function typeof(
        TypeScriptNode $type,
    ): self {
        return new self('typeof', $type);
    }

    public static function keyof(
        TypeScriptNode $type,
    ): self {
        return new self('keyof', $type);
    }

    public static function instanceof(
        TypeScriptNode $instance,
        TypeScriptNode $class,
    ): self {
        return new self('instanceof', $class, $instance);
    }

    public static function delete(
        TypeScriptNode $node,
    ): self {
        return new self('delete', $node);
    }

    public static function extends(
        TypeScriptNode $child,
        TypeScriptNode $parent,
    ): self {
        return new self('extends', $parent, $child);
    }

    public function write(WritingContext $context): string
    {
        if ($this->left === null) {
            return "{$this->operator} {$this->right->write($context)}";
        }

        return "{$this->left->write($context)} {$this->operator} {$this->right->write($context)}";
    }

    public function visitorProfile(): VisitorProfile
    {
        return VisitorProfile::create()->single('left', 'right');
    }
}
