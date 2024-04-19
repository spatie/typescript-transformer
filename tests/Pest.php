<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\Writers\NamespaceWriter;

function classesToTypeScript(
    array $classes,
    ?TransformationContext $transformationContext = null,
): string {
    $collection = new TransformedCollection();

    foreach ($classes as $class) {
        $collection->add(transformClass($class, $transformationContext));
    }

    $referenceMap = (new ConnectReferencesAction())->execute($collection);

    $writeableFile = (new NamespaceWriter('fakeFile'))->output($collection, $referenceMap)[0];

    return $writeableFile->contents;
}

function transformClass(
    string|object $class,
    ?ClassTransformer $transformer = null
): Transformed {
    $transformer ??= new AllClassTransformer();

    $transformTypesAction = new TransformTypesAction();

    [$transformed] = $transformTypesAction->execute(
        [$transformer],
        [is_string($class) ? $class : $class::class],
    );

    return $transformed;
}

function resolveObjectNode(
    string|object $class,
    ?ClassTransformer $transformer = null
): TypeScriptObject {
    return transformClass($class, $transformer)->typeScriptNode->type;
}

function resolvePropertyNode(
    string|object $class,
    string $property,
    ?ClassTransformer $transformer = null
): TypeScriptProperty {
    $objectNode = resolveObjectNode($class, $transformer);

    foreach ($objectNode->properties as $propertyNode) {
        if ($propertyNode->name instanceof TypeScriptIdentifier && $propertyNode->name->name === $property) {
            return $propertyNode;
        }
    }

    throw new Exception("Could not find node for property {$property}");
}
