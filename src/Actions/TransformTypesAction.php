<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use ReflectionException;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
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
            TypeScriptTransformerLog::resolve()->error(
                "Failed to reflect class `{$type}`"
            );

            return null;
        }

        if (count($reflection->getAttributes(Hidden::class)) > 0) {
            return null;
        }

        foreach ($transformers as $transformer) {
            $transformed = $transformer->transform(
                $reflection,
                TransformationContext::createFromReflection($reflection),
            );

            if ($transformed instanceof Transformed) {
                return $transformed;
            }
        }

        return null;
    }
}
