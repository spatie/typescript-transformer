<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Exception;
use Generator;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Exceptions\NoAutoDiscoverTypesPathsDefined;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveSplitTypesCollectionsAction
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

    /**
     * @return TypesCollection[]
     * @throws NoAutoDiscoverTypesPathsDefined
     */
    public function execute(): array
    {
        $collections = [];

        $paths = $this->config->getAutoDiscoverTypesPaths();

        if (empty($paths)) {
            throw NoAutoDiscoverTypesPathsDefined::create();
        }

        foreach ($this->resolveIterator($paths) as $class) {
            $transformedType = $this->resolveTransformedType($class);

            if ($transformedType === null) {
                continue;
            }
            $namespace = implode('/', $transformedType->getNamespaceSegments());
            if (!isset($collections[$namespace])) {
                $collections[$namespace] = new TypesCollection();
            }
            $collections[$namespace][] = $transformedType;
        }

        return $collections;
    }

    protected function resolveIterator(array $paths): Generator
    {
        $paths = array_map(
            fn (string $path) => is_dir($path) ? $path : dirname($path),
            $paths
        );

        foreach ($this->finder->in($paths) as $fileInfo) {
            try {
                $classes = (new ResolveClassesInPhpFileAction())->execute($fileInfo);

                foreach ($classes as $name) {
                    yield $name => new ReflectionClass($name);
                }
            } catch (Exception $exception) {
            }
        }
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
