<?php

use Spatie\TypeScriptTransformer\Actions\CollectAdditionalImportsAction;
use Spatie\TypeScriptTransformer\Attributes\AdditionalImport;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\TestSupport\MemoryWriter;
use Spatie\TypeScriptTransformer\Tests\TestSupport\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

function createConfig(string $outputDirectory): TypeScriptTransformerConfig
{
    return new TypeScriptTransformerConfig(
        outputDirectory: $outputDirectory,
        transformedProviders: [],
        typesWriter: new MemoryWriter(),
        formatter: null,
    );
}

it('collects additional imports from TypeScriptRaw nodes', function () {
    $import = new AdditionalImport('types/components.ts', 'SomeComponent');

    $transformed = TransformedFactory::alias(
        name: 'MyType',
        typeScriptNode: new TypeScriptRaw('SomeComponent', [$import]),
    )->build();

    $collection = new TransformedCollection([$transformed]);

    $action = new CollectAdditionalImportsAction(createConfig(sys_get_temp_dir()));
    $action->execute($collection);

    expect($transformed->additionalImports)->toHaveCount(1);
    expect($transformed->additionalImports[0]->path)->toBe('types/components.ts');
    expect($transformed->additionalImports[0]->names)->toBe(['SomeComponent']);
});

it('keeps relative import paths unchanged', function () {
    $import = new AdditionalImport('types/components.ts', 'SomeComponent');

    $transformed = TransformedFactory::alias(
        name: 'MyType',
        typeScriptNode: new TypeScriptRaw('SomeComponent', [$import]),
    )->build();

    $collection = new TransformedCollection([$transformed]);

    $action = new CollectAdditionalImportsAction(createConfig(sys_get_temp_dir()));
    $action->execute($collection);

    expect($transformed->additionalImports[0]->path)->toBe('types/components.ts');
});

it('normalizes absolute import paths relative to the output directory', function () {
    $outputDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'output';
    $importPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'types'.DIRECTORY_SEPARATOR.'components.ts';

    $import = new AdditionalImport($importPath, 'SomeComponent');

    $transformed = TransformedFactory::alias(
        name: 'MyType',
        typeScriptNode: new TypeScriptRaw('SomeComponent', [$import]),
    )->build();

    $collection = new TransformedCollection([$transformed]);

    $action = new CollectAdditionalImportsAction(createConfig($outputDir));
    $action->execute($collection);

    expect($transformed->additionalImports[0]->path)->toBe('../resources/types/components');
});
