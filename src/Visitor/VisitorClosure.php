<?php

namespace Spatie\TypeScriptTransformer\Visitor;

use Closure;
use ReflectionFunction;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

class VisitorClosure
{
    protected bool $requiresMetadata;

    public function __construct(
        protected Closure $closure,
        protected ?array $allowedNodes,
        protected VisitorClosureType $type,
    ) {
        $this->requiresMetadata = (new ReflectionFunction($this->closure))->getNumberOfParameters() === 2;
    }

    public function isBefore(): bool
    {
        return $this->type === VisitorClosureType::Before;
    }

    public function isAfter(): bool
    {
        return $this->type === VisitorClosureType::After;
    }

    public function shouldRun(
        TypeScriptNode $node
    ): bool {
        if ($this->allowedNodes === null) {
            return true;
        }

        return in_array(get_class($node), $this->allowedNodes);
    }

    public function run(
        TypeScriptNode $node,
        array &$metadata,
    ): VisitorOperation {
        $output = $this->requiresMetadata
            ? ($this->closure)($node, $metadata)
            : ($this->closure)($node);

        if ($output instanceof VisitorOperation) {
            return $output;
        }

        return VisitorOperation::keep();
    }
}
