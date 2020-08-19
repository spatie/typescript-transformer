<?php

namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Support\ClassReader;
use Spatie\TypescriptTransformer\Support\CollectedOccurrence;
use Spatie\TypescriptTransformer\Support\TransformerFactory;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class AnnotationCollector extends Collector
{
    protected ClassReader $classReader;

    private TransformerFactory $transformerFactory;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        parent::__construct($config);

        $this->classReader = new ClassReader();
        $this->transformerFactory = new TransformerFactory($config);
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

    protected function resolveTransformer(
        ReflectionClass $class,
        ?string $transformer
    ): Transformer {
        if ($transformer !== null) {
            return $this->transformerFactory->create($transformer);
        }

        foreach ($this->config->getTransformers() as $transformer) {
            if ($transformer->canTransform($class)) {
                return $transformer;
            }
        }

        throw TransformerNotFound::create($class);
    }
}
