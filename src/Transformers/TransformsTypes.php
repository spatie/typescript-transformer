<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TranspilationResult;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;
use Spatie\TypeScriptTransformer\TypeReflectors\TypeReflector;

trait TransformsTypes
{
    protected function reflectionToTypeScript(
        ReflectionMethod | ReflectionProperty | ReflectionParameter $reflection,
        MissingSymbolsCollection $missingSymbolsCollection,
        bool $nullablesAreOptional = false,
        TypeProcessor ...$typeProcessors
    ): ?TranspilationResult {
        $type = $this->reflectionToType(
            $reflection,
            $missingSymbolsCollection,
            ...$typeProcessors
        );

        if ($type === null) {
            return null;
        }

        return $this->typeToTypeScript(
            $type,
            $missingSymbolsCollection,
            $nullablesAreOptional,
            $reflection->getDeclaringClass()?->getName()
        );
    }

    protected function reflectionToType(
        ReflectionMethod | ReflectionProperty | ReflectionParameter $reflection,
        MissingSymbolsCollection $missingSymbolsCollection,
        TypeProcessor ...$typeProcessors
    ): ?Type {
        $type = TypeReflector::new($reflection)->reflect();

        foreach ($typeProcessors as $processor) {
            $type = $processor->process(
                $type,
                $reflection,
                $missingSymbolsCollection
            );

            if ($type === null) {
                return null;
            }
        }

        return $type;
    }

    protected function typeToTypeScript(
        Type $type,
        MissingSymbolsCollection $missingSymbolsCollection,
        bool $nullablesAreOptional = false,
        ?string $currentClass = null,
    ): TranspilationResult {
        $transpiler = new TranspileTypeToTypeScriptAction(
            $missingSymbolsCollection,
            $nullablesAreOptional,
            $currentClass,
        );

        return $transpiler->execute($type);
    }
}
