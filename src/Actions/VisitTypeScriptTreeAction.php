<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Closure;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNodeWithChildren;

class VisitTypeScriptTreeAction
{
    public function execute(
        TypeScriptNode $typeScriptNode,
        Closure $walker,
        array $allowedNodes = null
    ): void {
        // TODO: would be cool to replace nodes, remove them etc
        // Problem: nodes are sometimes structured in different properties which makes this complicated

        if ($allowedNodes !== null && in_array(get_class($typeScriptNode), $allowedNodes)) {
            $walker($typeScriptNode);
        }

        if ($typeScriptNode instanceof TypeScriptNodeWithChildren) {
            $children = array_values(array_filter($typeScriptNode->children()));

            foreach ($children as $child) {
                $this->execute($child, $walker, $allowedNodes);
            }
        }
    }
}
