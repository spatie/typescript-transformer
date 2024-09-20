<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class ExecuteProvidedClosuresAction
{
    protected Visitor $visitor;

    public function __construct(
        protected TypeScriptTransformerConfig $config
    ) {
        $this->visitor = Visitor::create()->closures(...$this->config->providedVisitorClosures);
    }

    /**
     * @param TransformedCollection|array<TypeScriptNode> $nodes
     */
    public function execute(
        TransformedCollection|array $nodes,
    ): void {
        if (empty($this->config->providedVisitorClosures)) {
            return;
        }

        $isTransformedCollection = $nodes instanceof TransformedCollection;

        foreach ($nodes as $node) {
            $this->visitor->execute($isTransformedCollection ? $node->typeScriptNode : $node);
        }
    }
}
