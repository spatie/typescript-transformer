<?php

use Spatie\TypeScriptTransformer\Actions\CollectAdditionalImportsAction;
use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\Reference;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Tests\TestSupport\MemoryWriter;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

function classesToTypeScript(
    array $classes,
    ?Transformer $transformer = null,
    ?TypeScriptTransformerConfig $config = null,
): string {
    $config ??= new TypeScriptTransformerConfig(
        outputDirectory: sys_get_temp_dir(),
        transformedProviders: [],
        typesWriter: new MemoryWriter(),
        formatter: null,
    );

    $collection = new TransformedCollection();

    foreach ($classes as $class) {
        $collection->add(transformSingle($class, $transformer));
    }

    (new ConnectReferencesAction(new NullLogger()))->execute($collection);

    (new CollectAdditionalImportsAction($config))->execute($collection);

    $writer = new MemoryWriter();

    foreach ($collection as $transformed) {
        $transformed->setWriter($writer);
    }

    ($writer)->output($collection->all(), $collection);

    return $writer->getOutput();
}

function transformSingle(
    string|object $class,
    ?Transformer $transformer = null,
    ?Reference $reference = null,
): Transformed|Untransformable {
    $transformer ??= new AllClassTransformer();

    $transformTypesAction = new TransformTypesAction();

    $results = $transformTypesAction->execute(
        [$transformer],
        [PhpClassNode::fromClassString(is_string($class) ? $class : $class::class)],
    );

    $result = $results[0] ?? Untransformable::create();

    if ($reference !== null && $result instanceof Transformed) {
        $result->reference = $reference;
    }

    return $result;
}
