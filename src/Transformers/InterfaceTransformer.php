<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptInterface;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptMethod;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptParameter;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptSingleType;

class InterfaceTransformer extends DtoTransformer implements Transformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isInterface();
    }

    public function transform(ReflectionClass $class, ?string $name = null): Transformed
    {
        $typeReferences = new TypeReferencesCollection();

        $items = [
            ...$this->transformProperties($class, $typeReferences),
            ...$this->transformMethods($class, $typeReferences),
            ...$this->transformExtra($class, $typeReferences),
        ];

        $named = TypeReference::fromFqcn($class->name, $name);

        $structure = new TypeScriptInterface(
            $named->getTypeScriptName(),
            array_values(array_filter(
                $items,
                fn (mixed $item) => $item instanceof TypeScriptProperty,
            )),
            array_values(array_filter(
                $items,
                fn (mixed $item) => $item instanceof TypeScriptMethod,
            ))
        );

        return new Transformed(
            name: $named,
            structure: $structure,
            typeReferences: $typeReferences
        );
    }

    protected function transformMethods(
        ReflectionClass $class,
        TypeReferencesCollection $typeReferences
    ): array {
        return collect($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->map(function (ReflectionMethod $method) use ($typeReferences) {
                $parameters = collect($method->getParameters())
                    ->map(function (ReflectionParameter $parameter) use ($typeReferences) {
                        $type = $this->reflectionToTypeScript(
                            $parameter,
                            $typeReferences,
                            ...$this->typeProcessors()
                        );

                        if ($type === null) {
                            return null;
                        }

                        return new TypeScriptParameter(
                            $parameter->name,
                            new TypeScriptSingleType($type),
                            $parameter->isOptional()
                        );
                    })
                    ->filter()
                    ->values()
                    ->all();

                $returnType = $method->hasReturnType()
                    ? $this->reflectionToTypeScript(
                        $method,
                        $typeReferences,
                        ...$this->typeProcessors()
                    )
                    : 'any';

                return new TypeScriptMethod(
                    $method->name,
                    $parameters,
                    new TypeScriptSingleType($returnType)
                );
            })
            ->filter()
            ->values()
            ->all();
    }
}
