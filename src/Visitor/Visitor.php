<?php

namespace Spatie\TypeScriptTransformer\Visitor;

use Closure;
use Exception;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptVisitableNode;

class Visitor
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<VisitorClosure> $beforeClosures
     * @param array<VisitorClosure> $afterClosures
     */
    public function __construct(
        protected array $beforeClosures = [],
        protected array $afterClosures = [],
    ) {
    }

    public function before(
        Closure $closure,
        ?array $allowedNodes = null,
    ): self {
        $this->beforeClosures[] = new VisitorClosure($closure, $allowedNodes);

        return $this;
    }

    public function after(
        Closure $closure,
        ?array $allowedNodes = null,
    ): self {
        $this->afterClosures[] = new VisitorClosure($closure, $allowedNodes);

        return $this;
    }

    public function execute(
        TypeScriptNode $node,
        array &$metadata = [],
    ): ?TypeScriptNode {
        foreach ($this->beforeClosures as $beforeClosure) {
            if ($beforeClosure->shouldRun($node)) {
                $operation = $beforeClosure->run($node, $metadata);

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
                    throw new Exception("Tried setting $singleNodeName on ".get_class($node)." to ".get_class($visited)." but failed.");
                }
            }

            foreach ($profile->iterableNodes as $iterableNodeName) {
                foreach ($node->$iterableNodeName as $key => $subNode) {
                    $node->$iterableNodeName[$key] = $this->execute($subNode, $metadata);
                }

                $node->$iterableNodeName = array_values(array_filter($node->$iterableNodeName));
            }
        }

        foreach ($this->afterClosures as $afterClosure) {
            if ($afterClosure->shouldRun($node)) {
                $operation = $afterClosure->run($node, $metadata);

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
