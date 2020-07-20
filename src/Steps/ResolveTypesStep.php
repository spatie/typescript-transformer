<?php

namespace Spatie\TypescriptTransformer\Steps;

use hanneskod\classtools\Iterator\ClassIterator;
use IteratorAggregate;
use ReflectionClass;
use Spatie\TypescriptTransformer\ClassIteratorFileFilter;
use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Collectors\Collector;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\Structures\TypesCollection;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesStep
{
    private Finder $finder;

    /** @var \Spatie\TypescriptTransformer\Collectors\Collector[] */
    private array $collectors;

    private TypeScriptTransformerConfig $config;

    public function __construct(Finder $finder, TypeScriptTransformerConfig $config)
    {
        $this->finder = $finder;

        $this->config = $config;

        $this->collectors = array_map(
            fn(string $collector) => new $collector($config),
            $this->config->getCollectors()
        );
    }

    public function execute(): TypesCollection
    {
        $this->config->ensureConfigIsValid();

        $collection = new TypesCollection();

        foreach ($this->resolveIterator() as $class) {
            $collector = $this->resolveCollector($class);

            if ($collector === null) {
                continue;
            }

            $typeOccurrence = $collector->getTypeOccurrence($class);

            $type = $typeOccurrence->transformer->transform(
                $class,
                $typeOccurrence->name
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

    private function resolveCollector(ReflectionClass $class): ?Collector
    {
        foreach ($this->collectors as $collector) {
            if ($collector->shouldTransform($class)) {
                return $collector;
            }
        }

        return null;
    }
}
