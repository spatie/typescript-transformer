<?php

namespace Spatie\TypeScriptTransformer\Support;

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
        $attributeArguments = ($node->getAttributes(TypeScript::class)[0] ?? null)?->getArguments() ?? [];

        $name = $attributeArguments['name'] ?? $node->getShortName();

        $nameSpaceSegments = $attributeArguments['location'] ?? explode('\\', $node->getNamespaceName());

        return new TransformationContext(
            $name,
            $nameSpaceSegments,
            count($node->getAttributes(Optional::class)) > 0,
        );
    }
}
