<?php

namespace Spatie\TypeScriptTransformer\Actions;

use hanneskod\classtools\Iterator\ClassIterator;
use IteratorAggregate;
use ReflectionClass;
use Spatie\TypeScriptTransformer\ClassIteratorFileFilter;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Exceptions\NoSearchingPathsDefined;
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

        $searchingPaths = $this->config->getSearchingPaths();

        if(empty($searchingPaths)){
            throw NoSearchingPathsDefined::create();
        }

        foreach ($this->resolveIterator($searchingPaths) as $class) {
            $collector = $this->resolveCollector($class);

            if ($collector === null) {
                continue;
            }

            $collection[] = $collector->getTransformedType($class);
        }

        return $collection;
    }

    protected function resolveIterator(array $searchingPaths): IteratorAggregate
    {
        $searchingPaths = array_map(
            fn(string $searchingPath) => is_dir($searchingPath) ? $searchingPath : dirname($searchingPath),
            $searchingPaths
        );

        $iterator = new ClassIterator($this->finder->in($searchingPaths));

        return $iterator->enableAutoloading();
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
