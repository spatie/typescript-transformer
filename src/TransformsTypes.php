<?php

namespace Spatie\TypeScriptTransformer;

use phpDocumentor\Reflection\Type;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Attributes\TransformAsTypescript;
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
        $type = $this->reflectionToType($reflection);

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

    protected function reflectionToType(
        ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection
    ): Type {
        $attributes = array_filter(
            $reflection->getAttributes(),
            fn(ReflectionAttribute $attribute) => $attribute->getName() === TransformAsTypescript::class
        );

        if (! empty($attributes)) {
            /** @var \Spatie\TypeScriptTransformer\Attributes\TransformAsTypescript $attribute */
            $attribute = current($attributes)->newInstance();

            return $attribute->getType();
        }

        $type = TypeReflector::new($reflection)->reflect();

        return $type;
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
