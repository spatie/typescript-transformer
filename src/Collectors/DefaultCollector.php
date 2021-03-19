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

        return $this->resolveTransformedType($reflector);
    }

    protected function resolveTransformedType(ClassTypeReflector $reflector): TransformedType
    {
        if ($reflector->getType()) {
            $missingSymbols = new MissingSymbolsCollection();
            $name = $reflector->getName();

            $transpiler = new TranspileTypeToTypeScriptAction(
                $missingSymbols,
                $name
            );

            return TransformedType::create(
                $reflector->getReflectionClass(),
                $reflector->getName(),
                "export type {$name} = {$transpiler->execute($reflector->getType())};"
            );
        }

        $transformerClass = $reflector->getTransformerClass();

        if ($transformerClass !== null) {
            if (! class_exists($transformerClass)) {
                throw InvalidTransformerGiven::classDoesNotExist(
                    $reflector->getReflectionClass(),
                    $transformerClass
                );
            }

            if (! is_subclass_of($transformerClass, Transformer::class)) {
                throw InvalidTransformerGiven::classIsNotATransformer(
                    $reflector->getReflectionClass(),
                    $transformerClass
                );
            }

            $transformer = $this->config->buildTransformer($transformerClass);

            return $transformer->transform($reflector->getReflectionClass(), $reflector->getName());
        }

        $foundTransformer = null;

        foreach ($this->config->getTransformers() as $transformer) {
            if ($transformer->canTransform($reflector->getReflectionClass())) {
                $foundTransformer = $transformer;

                break;
            }
        }

        if($foundTransformer === null){
            throw TransformerNotFound::create($reflector->getReflectionClass());
        }

        return $foundTransformer->transform($reflector->getReflectionClass(), $reflector->getName());
    }
}
