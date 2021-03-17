<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypeScriptTransformer\ClassReader;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TransformerFactory;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class AnnotationCollector extends Collector
{
    protected ClassReader $classReader;

    protected TransformerFactory $transformerFactory;

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

    public function getTransformedType(ReflectionClass $class): TransformedType
    {
        [
            'name' => $name,
            'transformer' => $transformer,
        ] = $this->classReader->forClass($class);

        $transformer = $this->resolveTransformer($class, $transformer);

        return $transformer->transform($class, $name);
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
