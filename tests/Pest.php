<?php

use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;

function transformClass(
    string|object $class,
    ?TransformationContext $transformationContext = null,
    ?ClassTransformer $transformer = null
): Transformed {
    $transformer ??= new class () extends ClassTransformer {
        protected function shouldTransform(ReflectionClass $reflection): bool
        {
            return true;
        }
    };

    $transformationContext ??= new TransformationContext(
        is_object($class) ? get_class($class) : $class,
        []
    );

    return $transformer->transform(new ReflectionClass($class), $transformationContext);
}

function resolveObjectNode(
    string|object $class,
    ?TransformationContext $transformationContext = null,
    ?ClassTransformer $transformer = null
): TypeScriptObject {
    return transformClass($class, $transformationContext, $transformer)->typeScriptNode->type;
}

function resolvePropertyNode(
    string|object $class,
    string $property,
    ?TransformationContext $transformationContext = null,
    ?ClassTransformer $transformer = null
): TypeScriptProperty {
    $objectNode = resolveObjectNode($class, $transformationContext, $transformer);

    foreach ($objectNode->properties as $propertyNode) {
        if ($propertyNode->name instanceof TypeScriptIdentifier && $propertyNode->name->name === $property) {
            return $propertyNode;
        }
    }

    throw new Exception("Could not find node for property {$property}");
}
