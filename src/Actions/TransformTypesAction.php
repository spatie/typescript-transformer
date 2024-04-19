<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use ReflectionException;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

class TransformTypesAction
{
    /**
     * @param array<Transformer> $transformers
     * @param array<class-string> $discoveredClasses
     *
     * @return array<Transformed>
     */
    public function execute(
        array $transformers,
        array $discoveredClasses,
    ): array {
        $types = [];

        foreach ($discoveredClasses as $discoveredClass) {
            $transformed = $this->transformType(
                $transformers,
                $discoveredClass
            );

            if ($transformed) {
                $types[] = $transformed;
            }
        }

        return $types;
    }

    /**
     * @param class-string $type
     */
    protected function transformType(
        array $transformers,
        string $type
    ): ?Transformed {
        try {
            $reflection = new ReflectionClass($type);
        } catch (ReflectionException) {
            // TODO: maybe add some kind of log?

            return null;
        }

        if (count($reflection->getAttributes(Hidden::class)) > 0) {
            return null;
        }

        foreach ($transformers as $transformer) {
            $transformed = $transformer->transform(
                $reflection,
                $this->createTransformationContext($reflection),
            );

            if ($transformed instanceof Transformed) {
                return $transformed;
            }
        }

        return null;
    }

    protected function createTransformationContext(
        ReflectionClass $reflection
    ): TransformationContext {
        $attribute = $this->getTypeScriptAttribute($reflection);

        $name = $attribute->name ?? $reflection->getShortName();

        $nameSpaceSegments = $attribute->location ?? explode('\\', $reflection->getNamespaceName());

        return new TransformationContext(
            $name,
            $nameSpaceSegments,
            count($reflection->getAttributes(Optional::class)) > 0,
        );
    }

    protected function getTypeScriptAttribute(ReflectionClass $reflection): ?TypeScript
    {
        $attribute = $reflection->getAttributes(TypeScript::class)[0] ?? null;

        return $attribute?->newInstance();
    }
}
