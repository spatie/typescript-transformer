<?php

namespace Spatie\TypeScriptTransformer\Actions;

use hanneskod\classtools\Iterator\ClassIterator;
use IteratorAggregate;
use ReflectionClass;
use Spatie\TypeScriptTransformer\ClassIteratorFileFilter;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Exceptions\NoSearchingPathsDefined;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
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

        if (empty($searchingPaths)) {
            throw NoSearchingPathsDefined::create();
        }

        foreach ($this->resolveIterator($searchingPaths) as $class) {
            $transformedType = $this->resolveTransformedType($class);

            if ($transformedType === null) {
                continue;
            }

            $collection[] = $transformedType;
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

    protected function resolveTransformedType(ReflectionClass $class): ?TransformedType
    {
        foreach ($this->collectors as $collector) {
            $transformedType = $collector->getTransformedType($class);

            if ($transformedType !== null) {
                return $transformedType;
            }
        }

        return null;
    }
}
