<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Tests\Support\MemoryWriter;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\Transformer;

function classesToTypeScript(
    array $classes,
    ?Transformer $transformer = null
): string {
    $collection = new TransformedCollection();

    foreach ($classes as $class) {
        $collection->add(transformSingle($class, $transformer));
    }

    $referenceMap = (new ConnectReferencesAction(TypeScriptTransformerLog::createNullLog()))->execute($collection);

    $writer = new MemoryWriter();

    ($writer)->output($collection, $referenceMap);

    return $writer->getOutput();
}

function transformSingle(
    string|object $class,
    ?Transformer $transformer = null
): Transformed|Untransformable {
    $transformer ??= new AllClassTransformer();

    $transformTypesAction = new TransformTypesAction();

    [$transformed] = $transformTypesAction->execute(
        [$transformer],
        [PhpClassNode::fromClassString(is_string($class) ? $class : $class::class)],
    );

    return $transformed ?? Untransformable::create();
}
