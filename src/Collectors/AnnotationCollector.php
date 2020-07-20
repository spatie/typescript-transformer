<?php

namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Structures\TypeOccurrence;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class AnnotationCollector implements Collector
{
    private TypeScriptTransformerConfig $config;

    private ClassReader $classReader;

    /** @var \Spatie\TypescriptTransformer\Transformers\Transformer[] */
    private array $transformers;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;

        $this->classReader = new ClassReader();

        $this->transformers = array_map(
            fn(string $transformer) => new $transformer,
            $this->config->getTransformers()
        );
    }

    public function shouldTransform(ReflectionClass $class): bool
    {
        return strpos($class->getDocComment(), '@typescript');
    }

    public function getTypeOccurrence(ReflectionClass $class): TypeOccurrence
    {
        [
            'name' => $name,
            'transformer' => $transformer,
        ] = $this->classReader->forClass($class);

        return TypeOccurrence::create(
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

        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($class)) {
                return $transformer;
            }
        }

        throw TransformerNotFound::create($class);
    }

}
