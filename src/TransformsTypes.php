<?php

namespace Spatie\TypeScriptTransformer;

use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;
use Spatie\TypeScriptTransformer\TypeReflectors\TypeReflector;

trait TransformsTypes
{
    protected function reflectionToTypeScript(
        ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection,
        MissingSymbolsCollection $missingSymbolsCollection,
        TypeProcessor ...$typeProcessors
    ): ?string {
        $type = TypeReflector::new($reflection)->reflect();

        foreach ($typeProcessors as $processor) {
            $type = $processor->process($type, $reflection);

            if ($type === null) {
                return null;
            }
        }

        return $this->typeToTypeScript(
            $type,
            $missingSymbolsCollection,
            $reflection->getDeclaringClass()?->getName()
        );
    }

    protected function typeToTypeScript(
        Type $type,
        MissingSymbolsCollection $missingSymbolsCollection,
        ?string $currentClass = null,
    ): string {
        $transpiler = new TranspileTypeToTypeScriptAction(
            $missingSymbolsCollection,
            $currentClass
        );

        return $transpiler->execute($type);
    }
}
