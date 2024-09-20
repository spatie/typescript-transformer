<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use Spatie\TypeScriptTransformer\Actions\ProvideTypesAction;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Tests\Support\InlineTypesProvider;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

it('can provide types based upon the config', function () {
    $stringProvider = new class () implements TypesProvider {
        public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
        {
            $types->add(
                TransformedFactory::alias('Foo', new TypeScriptString())->build(),
            );
        }
    };

    $config = TypeScriptTransformerConfigFactory::create()
        ->typesProvider(
            new InlineTypesProvider([
                TransformedFactory::alias('Bar', new TypeScriptString()),
            ]),
            $stringProvider::class
        )
        ->get();

    $types = (new ProvideTypesAction($config))->execute();

    expect($types)->toHaveCount(2);

    $typesArray = array_values($types->all());

    expect($typesArray[0]->getName())->toBe('Bar');
    expect($typesArray[1]->getName())->toBe('Foo');
});
