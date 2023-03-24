<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;
use Spatie\TypeScriptTransformer\TypeReflectors\TypeReflector;

trait TransformsTypes
{
    protected function reflectionToTypeScript(
        ReflectionMethod | ReflectionProperty | ReflectionParameter $reflection,
        TypeReferencesCollection $typeReferences,
        TypeProcessor ...$typeProcessors
    ): ?string {
        $type = $this->reflectionToType(
            $reflection,
            $typeReferences,
            ...$typeProcessors
        );

        if ($type === null) {
            return null;
        }

        return $this->typeToTypeScript(
            $type,
            $typeReferences,
            $reflection->getDeclaringClass()?->getName()
        );
    }

    protected function reflectionToType(
        ReflectionMethod | ReflectionProperty | ReflectionParameter $reflection,
        TypeReferencesCollection $typeReferences,
        TypeProcessor ...$typeProcessors
    ): ?Type {
        $type = TypeReflector::new($reflection)->reflect();

        foreach ($typeProcessors as $processor) {
            $type = $processor->process(
                $type,
                $reflection,
                $typeReferences
            );

            if ($type === null) {
                return null;
            }
        }

        return $type;
    }

    protected function typeToTypeScript(
        Type $type,
        TypeReferencesCollection $typeReferences,
        ?string $currentClass = null,
    ): string {
        $transpiler = new TranspileTypeToTypeScriptAction(
            $typeReferences,
            $currentClass,
        );

        return $transpiler->execute($type);
    }
}
