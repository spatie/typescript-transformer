<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Exception;
use Generator;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Exceptions\NoAutoDiscoverTypesPathsDefined;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesCollectionAction
{
    protected ResolveTransformedAction $resolveTransformedAction;

    public function __construct(
        protected Finder $finder,
        protected TypeScriptTransformerConfig $config
    ) {
        $this->resolveTransformedAction = new ResolveTransformedAction($this->config);
    }

    public function execute(): TypesCollection
    {
        $collection = new TypesCollection();

        $paths = $this->config->getAutoDiscoverTypesPaths();

        if (empty($paths)) {
            throw NoAutoDiscoverTypesPathsDefined::create();
        }

        foreach ($this->resolveIterator($paths) as $class) {
            $transformedType = $this->resolveTransformedAction->execute($class);

            if ($transformedType === null) {
                continue;
            }

            $collection->add($transformedType);
        }

        return $collection;
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
}
