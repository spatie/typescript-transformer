<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use Spatie\TypeScriptTransformer\Actions\ProvideTypesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\Support\Console\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Tests\Support\ArrayLogger;
use Spatie\TypeScriptTransformer\Tests\Support\InlineTypesProvider;
use Spatie\TypeScriptTransformer\TypeProviders\LoggingTypesProvider;
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

    $types = (new ProvideTypesAction($config))->execute(new NullLogger());

    expect($types)->toHaveCount(2);

    $typesArray = array_values($types->all());

    expect($typesArray[0]->getName())->toBe('Bar');
    expect($typesArray[1]->getName())->toBe('Foo');
});

it('provides logger to LoggingTypesProvider implementations', function () {
    $loggingProvider = new class () implements TypesProvider, LoggingTypesProvider {
        private Logger $logger;

        public function setLogger(Logger $logger): void
        {
            $this->logger = $logger;
        }

        public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
        {
            $this->logger->info('Logger was provided to LoggingTypesProvider');
        }
    };

    $config = TypeScriptTransformerConfigFactory::create()
        ->typesProvider($loggingProvider)
        ->get();

    $logger = new ArrayLogger([]);

    (new ProvideTypesAction($config))->execute($logger);

    expect($logger->logs)->toHaveCount(1);
    expect($logger->logs[0]['level'])->toBe('info');
    expect($logger->logs[0]['item'])->toBe('Logger was provided to LoggingTypesProvider');
});
