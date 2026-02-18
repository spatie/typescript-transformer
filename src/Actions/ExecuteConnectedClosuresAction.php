<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Traversable;

class ExecuteConnectedClosuresAction
{
    protected Visitor $visitor;

    public function __construct(
        protected TypeScriptTransformerConfig $config
    ) {
        $this->visitor = Visitor::create()->closures(...$this->config->connectedVisitorClosures);
    }

    /**
     * @param TransformedCollection|Traversable<Transformed> $nodes
     */
    public function execute(
        TransformedCollection|Traversable $nodes,
    ): void {
        if (empty($this->config->connectedVisitorClosures)) {
            return;
        }

        foreach ($nodes as $node) {
            $result = $this->visitor->execute($node->typeScriptNode);

            if ($result !== null) {
                $node->typeScriptNode = $result;
            }
        }
    }
}
