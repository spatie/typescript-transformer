<?php

namespace Spatie\TypescriptTransformer\Actions;

use hanneskod\classtools\Iterator\ClassIterator;
use ReflectionClass;
use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Finder\Finder;

class ResolveTypesCollectionAction
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

        $this->classReader = new ClassReader($config->getDefaultFile());

        $this->transformers = array_map(
            fn (string $transformer) => new $transformer,
            $this->config->getTransformers()
        );
    }

    public function execute(): TypesCollection
    {
        $this->config->ensureConfigIsValid();

        $typesCollection = new TypesCollection();

        foreach ($this->resolveIterator() as $class) {
            if (strpos($class->getDocComment(), '@typescript') === false) {
                continue;
            }

            [
                'file' => $file,
                'name' => $name,
                'transformer' => $transformer,
            ] = $this->classReader->forClass($class);

            $transformer = $this->resolveTransformer($class, $transformer);

            $typesCollection->add(new Type(
                $class,
                $file,
                $name,
                $transformer->transform($class, $name)
            ));
        }

        return $typesCollection;
    }

    private function resolveIterator(): ClassIterator
    {
        $iterator = new ClassIterator($this->finder->in(
            $this->config->getSearchingPath()
        ));

        $iterator->enableAutoloading();

        return $iterator;
    }

    private function resolveTransformer(ReflectionClass $class, ?string $transformer): Transformer
    {
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
