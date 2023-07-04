<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use ReflectionException;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class TransformTypesAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
    ) {
    }

    /**
     * @param  array<string>  $types
     * @return array<Transformed>
     */
    public function execute(array $types): array
    {
        $transformedTypes = [];

        foreach ($types as $type) {
            try {
                $reflection = new ReflectionClass($type);
            } catch (ReflectionException) {
                // TODO: maybe add some kind of log?

                continue;
            }

            foreach ($this->config->transformers as $transformer) {
                $transformed = $transformer->transform(
                    $reflection,
                    $this->createTransformationContext($reflection),
                );

                if ($transformed instanceof Transformed) {
                    $transformedTypes[] = $transformed;

                    break;
                }
            }
        }

        return $transformedTypes;
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
