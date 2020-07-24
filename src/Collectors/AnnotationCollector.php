<?php

namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypescriptTransformer\ValueObjects\ClassOccurrence;

class AnnotationCollector extends Collector
{
    private ClassReader $classReader;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        parent::__construct($config);

        $this->classReader = new ClassReader();
    }

    public function shouldTransform(ReflectionClass $class): bool
    {
        return strpos($class->getDocComment(), '@typescript');
    }

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence
    {
        [
            'name' => $name,
            'transformer' => $transformer,
        ] = $this->classReader->forClass($class);

        return ClassOccurrence::create(
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
