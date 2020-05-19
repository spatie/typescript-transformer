<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;
use hanneskod\classtools\Iterator\ClassIterator;
use Illuminate\Support\Str;
use ReflectionClass;
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
            fn(string $transformer) => new $transformer,
            $this->config->getTransformers()
        );
    }

    public function execute(): TypesCollection
    {
        $typesCollection = new TypesCollection();

        foreach ($this->resolveIterator() as $class) {
            if (! Str::contains($class->getDocComment(), '@typescript')) {
                continue;
            }

            ['file' => $file, 'name' => $name] = $this->classReader->forClass($class);

            $transformer = $this->findTransformer($class);

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

    private function findTransformer(ReflectionClass $class): Transformer
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($class)) {
                return $transformer;
            }
        }

        throw TransformerNotFound::create($class);
    }
}
