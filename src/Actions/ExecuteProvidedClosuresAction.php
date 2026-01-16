<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Traversable;

class ExecuteProvidedClosuresAction
{
    protected Visitor $visitor;

    public function __construct(
        protected TypeScriptTransformerConfig $config
    ) {
        $this->visitor = Visitor::create()->closures(...$this->config->providedVisitorClosures);
    }

    /**
     * @param TransformedCollection|Traversable<Transformed> $nodes
     */
    public function execute(
        TransformedCollection|Traversable $nodes,
    ): void {
        if (empty($this->config->providedVisitorClosures)) {
            return;
        }

        foreach ($nodes as $node) {
            $this->visitor->execute($node->typeScriptNode);
        }
    }
}
