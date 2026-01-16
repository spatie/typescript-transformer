<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use Spatie\TypeScriptTransformer\Actions\RunProvidersAction;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Tests\Support\ArrayLogger;
use Spatie\TypeScriptTransformer\Tests\Support\InlineTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

it('can provide types based upon the config', function () {
    $stringProvider = new class () implements TransformedProvider {
        public function provide(TypeScriptTransformerConfig $config): array
        {
            return [
                TransformedFactory::alias('Foo', new TypeScriptString())->build(),
            ];
        }
    };

    $config = TypeScriptTransformerConfigFactory::create()
        ->provider(
            new InlineTransformedProvider([
                TransformedFactory::alias('Bar', new TypeScriptString()),
            ]),
            $stringProvider::class
        )
        ->get();

    $types = (new RunProvidersAction($config))->execute(new NullLogger());

    expect($types)->toHaveCount(2);

    $typesArray = array_values($types->all());

    expect($typesArray[0]->getName())->toBe('Bar');
    expect($typesArray[1]->getName())->toBe('Foo');
});

it('provides logger to LoggingTransformedProvider implementations', function () {
    $loggingProvider = new class () implements TransformedProvider, LoggingTransformedProvider {
        private Logger $logger;

        public function setLogger(Logger $logger): void
        {
            $this->logger = $logger;
        }

        public function provide(TypeScriptTransformerConfig $config): array
        {
            $this->logger->info('Logger was provided to LoggingTransformedProvider');

            return [];
        }
    };

    $config = TypeScriptTransformerConfigFactory::create()
        ->provider($loggingProvider)
        ->get();

    $logger = new ArrayLogger([]);

    (new RunProvidersAction($config))->execute($logger);

    expect($logger->logs)->toHaveCount(1);
    expect($logger->logs[0]['level'])->toBe('info');
    expect($logger->logs[0]['item'])->toBe('Logger was provided to LoggingTransformedProvider');
});
