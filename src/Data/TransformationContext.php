<?php

namespace Spatie\TypeScriptTransformer\Data;

use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class TransformationContext
{
    public function __construct(
        public string $name,
        public array $nameSpaceSegments,
        public bool $optional = false,
    ) {
    }

    public static function createFromPhpClass(
        PhpClassNode $node
    ): TransformationContext {
        $attribute = $node->getAttributes(TypeScript::class)[0] ?? null;

        $name = $attribute && $attribute->hasArgument('name') && $attribute->getArgument('name') !== null
            ? $attribute->getArgument('name')
            : $node->getShortName();

        $nameSpaceSegments = $attribute && $attribute->hasArgument('location') && $attribute->getArgument('location') !== null
            ? $attribute->getArgument('location')
            : explode('\\', $node->getNamespaceName());

        return new TransformationContext(
            $name,
            $nameSpaceSegments,
            count($node->getAttributes(Optional::class)) > 0,
        );
    }
}
