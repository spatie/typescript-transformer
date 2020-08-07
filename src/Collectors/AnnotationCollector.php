<?php

namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Support\ClassReader;
use Spatie\TypescriptTransformer\Support\CollectedOccurrence;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class AnnotationCollector extends Collector
{
    private ClassReader $classReader;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        parent::__construct($config);

        $this->classReader = new ClassReader();
    }

    public function shouldCollect(ReflectionClass $class): bool
    {
        return (bool) strpos($class->getDocComment(), '@typescript');
    }

    public function getCollectedOccurrence(ReflectionClass $class): CollectedOccurrence
    {
        [
            'name' => $name,
            'transformer' => $transformer,
        ] = $this->classReader->forClass($class);

        return CollectedOccurrence::create(
            $this->resolveTransformer($class, $transformer),
            $name
        );
    }

    private function resolveTransformer(
        ReflectionClass $class,
        ?string $transformer
    ): Transformer {
        if ($transformer !== null) {
            return new $transformer;
        }

        foreach ($this->config->getTransformers() as $transformer) {
            if ($transformer->canTransform($class)) {
                return $transformer;
            }
        }

        throw TransformerNotFound::create($class);
    }
}
