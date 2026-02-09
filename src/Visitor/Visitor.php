<?php

namespace Spatie\TypeScriptTransformer\Visitor;

use Closure;
use Spatie\TypeScriptTransformer\TypeScriptNodeRegistry;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class Visitor
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<VisitorClosure> $closures
     */
    public function __construct(
        protected array $closures = [],
    ) {
    }

    public function before(
        Closure $closure,
        ?array $allowedNodes = null,
    ): self {
        $this->closures[] = new VisitorClosure($closure, $allowedNodes, VisitorClosureType::Before);

        return $this;
    }

    public function after(
        Closure $closure,
        ?array $allowedNodes = null,
    ): self {
        $this->closures[] = new VisitorClosure($closure, $allowedNodes, VisitorClosureType::After);

        return $this;
    }

    public function closures(
        VisitorClosure ...$closures
    ): self {
        array_push($this->closures, ...$closures);

        return $this;
    }

    public function execute(
        TypeScriptNode $node,
        array &$metadata = [],
    ): ?TypeScriptNode {
        foreach ($this->closures as $closure) {
            if (! $closure->isBefore()) {
                continue;
            }

            if ($closure->shouldRun($node)) {
                $operation = $closure->run($node, $metadata);

                if ($operation->type === VisitorOperationType::Remove) {
                    return null;
                }

                if ($operation->type === VisitorOperationType::Replace) {
                    return $operation->node;
                }
            }
        }

        foreach (TypeScriptNodeRegistry::resolve($node) as $propertyName) {
            $value = $node->$propertyName;

            if ($value === null) {
                continue;
            }

            if (! is_array($value)) {
                $node->$propertyName = $this->execute($value, $metadata);

                continue;
            }

            foreach ($value as $key => $subNode) {
                $value[$key] = $this->execute($subNode, $metadata);
            }

            $node->$propertyName = array_values(array_filter($value));
        }

        foreach ($this->closures as $closure) {
            if (! $closure->isAfter()) {
                continue;
            }

            if ($closure->shouldRun($node)) {
                $operation = $closure->run($node, $metadata);

                if ($operation->type === VisitorOperationType::Remove) {
                    return null;
                }

                if ($operation->type === VisitorOperationType::Replace) {
                    return $operation->node;
                }
            }
        }

        return $node;
    }
}
