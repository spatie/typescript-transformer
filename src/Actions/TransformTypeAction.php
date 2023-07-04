<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use ReflectionException;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformTypeAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        protected TypeScriptTransformerLog $log,
    ) {
    }

    /**
     * @param class-string $type
     */
    public function execute(string $type): ?Transformed
    {
        try {
            $reflection = new ReflectionClass($type);
        } catch (ReflectionException) {
            // TODO: maybe add some kind of log?

            return null;
        }

        foreach ($this->config->transformers as $transformer) {
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
        $name = $reflection->getShortName();

        $nameSpaceSegments = explode('\\', $reflection->getNamespaceName());

        return new TransformationContext(
            $name,
            $nameSpaceSegments,
        );
    }
}
