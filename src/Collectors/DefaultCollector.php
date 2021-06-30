<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeReflectors\ClassTypeReflector;

class DefaultCollector extends Collector
{
    public function getTransformedType(ReflectionClass $class): ?TransformedType
    {
        $reflector = ClassTypeReflector::create($class);

        if (! $reflector->isTransformable()) {
            return null;
        }

        $transformedType = $reflector->getType()
            ? $this->resolveAlreadyTransformedType($reflector)
            : $this->resolveTypeViaTransformer($reflector);

        if ($reflector->isInline()) {
            $transformedType->name = null;
            $transformedType->isInline = true;
        }

        return $transformedType;
    }

    protected function resolveAlreadyTransformedType(ClassTypeReflector $reflector): TransformedType
    {
        $missingSymbols = new MissingSymbolsCollection();
        $name = $reflector->getName();

        $transpiler = new TranspileTypeToTypeScriptAction(
            $missingSymbols,
            $name
        );

        return TransformedType::create(
            $reflector->getReflectionClass(),
            $reflector->getName(),
            $transpiler->execute($reflector->getType()),
            $missingSymbols
        );
    }

    protected function resolveTypeViaTransformer(ClassTypeReflector $reflector): ?TransformedType
    {
        $transformerClass = $reflector->getTransformerClass();

        if ($transformerClass !== null) {
            return $this->resolveTypeViaPredefinedTransformer($reflector);
        }

        foreach ($this->config->getTransformers() as $transformer) {
            $transformed = $transformer->transform(
                $reflector->getReflectionClass(),
                $reflector->getName()
            );

            if ($transformed !== null) {
                return $transformed;
            }
        }

        throw TransformerNotFound::create($reflector->getReflectionClass());
    }

    protected function resolveTypeViaPredefinedTransformer(ClassTypeReflector $reflector): ?TransformedType
    {
        if (! class_exists($reflector->getTransformerClass())) {
            throw InvalidTransformerGiven::classDoesNotExist(
                $reflector->getReflectionClass(),
                $reflector->getTransformerClass()
            );
        }

        if (! is_subclass_of($reflector->getTransformerClass(), Transformer::class)) {
            throw InvalidTransformerGiven::classIsNotATransformer(
                $reflector->getReflectionClass(),
                $reflector->getTransformerClass()
            );
        }

        $transformer = $this->config->buildTransformer($reflector->getTransformerClass());

        return $transformer->transform(
            $reflector->getReflectionClass(),
            $reflector->getName()
        );
    }
}
