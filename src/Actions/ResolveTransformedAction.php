<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptRaw;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeReflectors\ClassTypeReflector;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class ResolveTransformedAction
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    public function execute(ReflectionClass $class): ?Transformed
    {
        $reflector = ClassTypeReflector::create($class);

        if (! $reflector->isTransformable()) {
            return $this->resolveViaTransformers($class, $reflector->getName(), onlyAuto: true);
        }

        if ($reflector->getType()) {
            return $this->resolveViaAlreadyTransformedType($reflector);
        }

        $transformed = $this->resolveViaTransformer($reflector);

        $transformed->inline = $reflector->isInline();

        return $transformed;
    }

    protected function resolveViaAlreadyTransformedType(ClassTypeReflector $reflector): Transformed
    {
        $typeReferences = new TypeReferencesCollection();

        $named = TypeReference::fromFqcn($reflector->getReflectionClass()->getName(), $reflector->getName());

        $transpiler = new TranspileTypeToTypeScriptAction(
            $typeReferences,
            $reflector->getReflectionClass()->getName()
        );

        $transpiled = new TypeScriptRaw($transpiler->execute($reflector->getType()));

        return new Transformed(
            $named,
            $reflector->isInline()
                ? $transpiled
                : new TypeScriptAlias($named->getTypeScriptName(), $transpiled),
            new TypeReferencesCollection(),
            $reflector->isInline(),
        );
    }

    protected function resolveViaTransformer(ClassTypeReflector $reflector): Transformed
    {
        $transformerClass = $reflector->getTransformerClass();

        if ($transformerClass !== null) {
            return $this->resolveViaPredefinedTransformer($reflector);
        }

        $transformed = $this->resolveViaTransformers(
            $reflector->getReflectionClass(),
            $reflector->getName(),
            onlyAuto: false,
        );


        if ($transformed !== null) {
            return $transformed;
        }

        throw TransformerNotFound::create($reflector->getReflectionClass());
    }

    protected function resolveViaPredefinedTransformer(ClassTypeReflector $reflector): Transformed
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

    protected function resolveViaTransformers(
        ReflectionClass $class,
        ?string $name,
        bool $onlyAuto
    ): ?Transformed {
        foreach ($this->config->getTransformers($onlyAuto) as $transformer) {
            if ($transformer->canTransform($class)) {
                return $transformer->transform($class, $name);
            }
        }

        return null;
    }
}
