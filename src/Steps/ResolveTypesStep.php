<?php

namespace Spatie\TypescriptTransformer\Steps;

use hanneskod\classtools\Iterator\ClassIterator;
use IteratorAggregate;
use ReflectionClass;
use Spatie\TypescriptTransformer\ClassIteratorFileFilter;
use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesStep
{
    private Finder $finder;

    private ClassReader $classReader;

    /** @var \Spatie\TypescriptTransformer\Transformers\Transformer[] */
    private array $transformers;

    private TypeScriptTransformerConfig $config;

    public function __construct(Finder $finder, TypeScriptTransformerConfig $config)
    {
        $this->finder = $finder;

        $this->config = $config;

        $this->classReader = new ClassReader();

        $this->transformers = array_map(
            fn (string $transformer) => new $transformer,
            $this->config->getTransformers()
        );
    }

    public function execute(): TypesCollection
    {
        $this->config->ensureConfigIsValid();

        $collection = new TypesCollection();

        foreach ($this->resolveIterator() as $class) {
            if (strpos($class->getDocComment(), '@typescript') === false) {
                continue;
            }

            [
                'name' => $name,
                'transformer' => $transformer,
            ] = $this->classReader->forClass($class);

            $type = $this->resolveTransformer($class, $transformer)->transform(
                $class,
                $name
            );

            $collection->add($type);
        }

        return $collection;
    }

    private function resolveIterator(): IteratorAggregate
    {
        $searchingPath = is_dir($this->config->getSearchingPath())
            ? $this->config->getSearchingPath()
            : dirname($this->config->getSearchingPath());

        $iterator = new ClassIterator($this->finder->in($searchingPath));

        $iterator->enableAutoloading();

        if (is_file($this->config->getSearchingPath())) {
            return $iterator->filter(
                new ClassIteratorFileFilter($this->config->getSearchingPath())
            );
        }

        return $iterator;
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
