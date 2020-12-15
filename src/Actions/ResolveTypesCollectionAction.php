<?php

namespace Spatie\TypeScriptTransformer\Actions;

use hanneskod\classtools\Iterator\ClassIterator;
use IteratorAggregate;
use ReflectionClass;
use Spatie\TypeScriptTransformer\ClassIteratorFileFilter;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesCollectionAction
{
    protected Finder $finder;

    /** @var \Spatie\TypeScriptTransformer\Collectors\Collector[] */
    protected array $collectors;

    protected TypeScriptTransformerConfig $config;

    public function __construct(Finder $finder, TypeScriptTransformerConfig $config)
    {
        $this->finder = $finder;

        $this->config = $config;

        $this->collectors = $config->getCollectors();
    }

    public function execute(): TypesCollection
    {
        $collection = new TypesCollection();

        foreach ($this->resolveIterator() as $class) {
            $collector = $this->resolveCollector($class);

            if ($collector === null) {
                continue;
            }

            $collectedOccurrence = $collector->getCollectedOccurrence($class);

            $type = $collectedOccurrence->transformer->transform(
                $class,
                $collectedOccurrence->name
            );

            $collection[] = $type;
        }

        return $collection;
    }

    protected function resolveIterator(): IteratorAggregate
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

    protected function resolveCollector(ReflectionClass $class): ?Collector
    {
        foreach ($this->collectors as $collector) {
            if ($collector->shouldCollect($class)) {
                return $collector;
            }
        }

        return null;
    }
}
