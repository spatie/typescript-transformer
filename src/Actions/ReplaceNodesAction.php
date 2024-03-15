<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

class ReplaceNodesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config
    ) {
    }

    /**
     * @param array<array{search: TypeScriptNode, replacement: TypeScriptNode}> $replacements
     */
    public function execute(
        TransformedCollection $transformedCollection,
    ): void {
        if (empty($this->config->nodeReplacements)) {
            return;
        }

        $visitor = Visitor::create();

        foreach ($this->config->nodeReplacements as $replacement) {
            $visitor->before(
                function (TypeScriptNode $node) use ($replacement) {
                    if ($node != $replacement['search']) {
                        return;
                    }

                    return VisitorOperation::replace($replacement['replacement']);
                },
                [$replacement['search']::class]
            );
        }

        foreach ($transformedCollection as $transformed) {
            $visitor->execute($transformed->typeScriptNode);
        }
    }
}
