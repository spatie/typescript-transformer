<?php

namespace Spatie\TypeScriptTransformer\Support;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

class TransformationContext
{
    public function __construct(
        public string $name,
        public array $nameSpaceSegments,
        public bool $optional = false,
    ) {
    }

    public static function createFromReflection(
        ReflectionClass $reflection
    ): TransformationContext {
        $attribute = ($reflection->getAttributes(TypeScript::class)[0] ?? null)?->newInstance();

        $name = $attribute?->name ?? $reflection->getShortName();

        $nameSpaceSegments = $attribute?->location ?? explode('\\', $reflection->getNamespaceName());

        return new TransformationContext(
            $name,
            $nameSpaceSegments,
            count($reflection->getAttributes(Optional::class)) > 0,
        );
    }
}
