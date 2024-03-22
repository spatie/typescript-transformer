<?php

namespace Spatie\TypeScriptTransformer\Visitor\Common;

use Closure;
use phpDocumentor\Reflection\Types\ClassString;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosure;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosureType;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

class ReplaceTypesVisitorClosure extends VisitorClosure
{
    /** @var array<string, TypeScriptNode|Closure> */
    protected static array $replacements = [];

    /** @var array<string, null> */
    protected static array $skip = [];

    /**
     * @param array<string|ClassString, TypeScriptNode|Closure> $typeReplacements
     */
    public function __construct(
        protected array $typeReplacements
    ) {
        parent::__construct(
            $this->resolveClosure(),
            allowedNodes: [TypeReference::class],
            type: VisitorClosureType::Before
        );

        static::$replacements = $typeReplacements;
    }

    protected function resolveClosure(): Closure
    {
        return function (TypeReference $node) {
            if (! $node->reference instanceof ClassStringReference) {
                return $node;
            }

            $class = $node->reference->classString;

            if (array_key_exists($class, static::$skip)) {
                return $node;
            }

            if (! array_key_exists($class, static::$replacements)) {
                foreach ($this->typeReplacements as $type => $replacement) {
                    if ($class === $type || is_a($class, $type, true)) {
                        static::$replacements[$class] = $replacement;

                        return $this->replaceNode($node, $replacement);
                    }
                }

                return $node;
            }

            return $this->replaceNode($node, static::$replacements[$class]);
        };
    }

    protected function replaceNode(
        TypeReference $node,
        Closure|TypeScriptNode $replacement,
    ): VisitorOperation {
        if ($replacement instanceof Closure) {
            $replacement = $replacement($node);
        }

        return VisitorOperation::replace($replacement);
    }
}
