<?php

namespace Spatie\TypeScriptTransformer\Visitor;

use Closure;
use Exception;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVisitableNode;

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

        if ($node instanceof TypeScriptVisitableNode) {
            $profile = $node->visitorProfile();

            foreach ($profile->singleNodes as $singleNodeName) {
                $subNode = $node->$singleNodeName;

                if ($subNode === null) {
                    continue;
                }

                $visited = $this->execute($subNode, $metadata);

                try {
                    $node->$singleNodeName = $visited;
                } catch (Exception $e) {
                    throw new Exception("Tried setting $singleNodeName on ".get_class($node).' to '.get_class($visited).' but failed.');
                }
            }

            foreach ($profile->iterableNodes as $iterableNodeName) {
                foreach ($node->$iterableNodeName as $key => $subNode) {
                    $node->$iterableNodeName[$key] = $this->execute($subNode, $metadata);
                }

                $node->$iterableNodeName = array_values(array_filter($node->$iterableNodeName));
            }
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
