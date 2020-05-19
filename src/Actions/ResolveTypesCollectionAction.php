<?php

namespace Spatie\TypescriptTransformer\Actions;

use Spatie\TypescriptTransformer\ClassReader;
use Spatie\TypescriptTransformer\Exceptions\MapperNotFound;
use Spatie\TypescriptTransformer\Mappers\Mapper;
use Spatie\TypescriptTransformer\Type;
use Spatie\TypescriptTransformer\TypesCollection;
use hanneskod\classtools\Iterator\ClassIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ResolveTypesCollectionAction
{
    private Finder $finder;

    private ClassReader $classReader;

    private Collection $mappers;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;

        $this->classReader = new ClassReader(
            config('typescript-transformer.default_file')
        );

        $this->mappers = collect(
            config('typescript-transformer.mappers')
        )->map(fn (string $mapper) => new $mapper);
    }

    public function execute(): TypesCollection
    {
        $typesCollection = new TypesCollection();

        foreach ($this->resolveIterator() as $class) {
            if (! Str::contains($class->getDocComment(), '@typescript')) {
                continue;
            }

            $classData = $this->classReader->forClass($class);

            $typesCollection->add(new Type(
                $class,
                $classData['file'],
                $classData['type'],
                $this->resolveMapper($class)->map($class)
            ));
        }

        return $typesCollection;
    }

    private function resolveIterator(): ClassIterator
    {
        $iterator = new ClassIterator($this->finder->in(
            config('typescript-transformer.searching_path')
        ));

        $iterator->enableAutoloading();

        return $iterator;
    }

    private function resolveMapper(ReflectionClass $class): Mapper
    {
        $mapper = $this->mappers
            ->first(fn (Mapper $mapper) => $mapper->isValid($class));

        if ($mapper === null) {
            throw MapperNotFound::create($class);
        }

        return $mapper;
    }
}
