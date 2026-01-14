<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use Spatie\TypeScriptTransformer\Actions\RunProvidersAction;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\Support\Console\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Tests\Support\ArrayLogger;
use Spatie\TypeScriptTransformer\Tests\Support\InlineTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\LoggingTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\StandaloneWritingTransformedProvider;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Spatie\TypeScriptTransformer\Writers\ModuleWriter;
use Spatie\TypeScriptTransformer\Writers\Writer;

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

    [$types, $writers] = (new RunProvidersAction($config))->execute(new NullLogger());

    expect($types)->toHaveCount(2);
    expect($writers->getStandaloneWriters())->toHaveCount(0);

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

it('sets writers correctly on transformed objects', function () {
    $standaloneProvider = new class () implements TransformedProvider, StandaloneWritingTransformedProvider {
        public function getWriter(): Writer
        {
            return new FlatModuleWriter('standalone.ts');
        }

        public function provide(TypeScriptTransformerConfig $config): array
        {
            return [
                TransformedFactory::alias('Standalone', new TypeScriptString())->build(),
            ];
        }
    };

    $config = TypeScriptTransformerConfigFactory::create()
        ->writer(new ModuleWriter())
        ->provider(
            new InlineTransformedProvider([
                TransformedFactory::alias('Regular', new TypeScriptString()),
            ]),
            $standaloneProvider
        )
        ->get();

    [$types, $writers] = (new RunProvidersAction($config))->execute(new NullLogger());

    $typesArray = array_values($types->all());

    expect($typesArray[0]->getName())->toBe('Regular');
    expect($typesArray[0]->getWriter())->toBeInstanceOf(ModuleWriter::class);

    expect($typesArray[1]->getName())->toBe('Standalone');
    expect($typesArray[1]->getWriter())->toBeInstanceOf(FlatModuleWriter::class);
});
