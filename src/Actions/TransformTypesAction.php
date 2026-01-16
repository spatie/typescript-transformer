<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class TransformTypesAction
{
    /**
     * @param array<Transformer> $transformers
     * @param array<PhpClassNode> $discoveredClasses
     *
     * @return array<Transformed>
     */
    public function execute(
        array $transformers,
        array $discoveredClasses,
    ): array {
        $types = [];

        foreach ($discoveredClasses as $discoveredClass) {
            $transformed = $this->transformClassNode(
                $transformers,
                $discoveredClass
            );

            if ($transformed) {
                $types[] = $transformed;
            }
        }

        return $types;
    }

    public function transformClassNode(
        array $transformers,
        PhpClassNode $node
    ): ?Transformed {
        if (count($node->getAttributes(Hidden::class)) > 0) {
            return null;
        }

        $transformationContext = TransformationContext::createFromPhpClass($node);

        foreach ($transformers as $transformer) {
            $transformed = $transformer->transform(
                $node,
                $transformationContext,
            );

            if ($transformed instanceof Transformed) {
                return $transformed;
            }
        }

        return null;
    }
}
