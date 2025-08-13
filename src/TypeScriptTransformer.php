<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\ExecuteConnectedClosuresAction;
use Spatie\TypeScriptTransformer\Actions\ExecuteProvidedClosuresAction;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Actions\ProvideTypesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Actions\WriteFilesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\Support\Console\NullLogger;
use Spatie\TypeScriptTransformer\Support\LoadPhpClassNodeAction;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;

class TypeScriptTransformer
{
    public function __construct(
        public readonly TypeScriptTransformerConfig $config,
        public readonly TypeScriptTransformerLog $log,
        public readonly DiscoverTypesAction $discoverTypesAction,
        public readonly ProvideTypesAction $provideTypesAction,
        public readonly ExecuteProvidedClosuresAction $executeProvidedClosuresAction,
        public readonly ConnectReferencesAction $connectReferencesAction,
        public readonly ExecuteConnectedClosuresAction $executeConnectedClosuresAction,
        public readonly WriteFilesAction $writeFilesAction,
        public readonly FormatFilesAction $formatFilesAction,
        public readonly TransformTypesAction $transformTypesAction,
        public readonly LoadPhpClassNodeAction $loadPhpClassNodeAction,
        public readonly bool $watch = false,
    ) {

    }

    public static function create(
        TypeScriptTransformerConfig|TypeScriptTransformerConfigFactory $config,
        Logger $console = new NullLogger(),
        bool $watch = false,
    ): self {
        $config = $config instanceof TypeScriptTransformerConfigFactory ? $config->get() : $config;

        $log = TypeScriptTransformerLog::create($console);

        return new self(
            $config,
            $log,
            new DiscoverTypesAction(),
            new ProvideTypesAction($config),
            new ExecuteProvidedClosuresAction($config),
            new ConnectReferencesAction($log),
            new ExecuteConnectedClosuresAction($config),
            new WriteFilesAction($config),
            new FormatFilesAction($config),
            new TransformTypesAction(),
            new LoadPhpClassNodeAction(),
            $watch
        );
    }

    public function execute(): void
    {
        /**
         * TODO:
         * - Add interface implementation + tests -> OK
         * - Split off Laravel specific code and test
         * - Split off data specific code and test
         * - Add support for watching files -> ok, maybe add docs and some tests
         * - Further write docs + check them -> only Laravel specific stuff
         * - Check old Laravel tests if we missed something
         * - Check in Flare whether everything is working as expected -> PR ready, needs fixing TS
         * - Fix todos
         * - Write some text arround refactoring in IDE and watcher, that IDE's useally take some time to write eveyrthing out so it can take up to 10 seconds before the watcher kicks in
         * - Make sure nullables can be exported as optional: https://github.com/spatie/typescript-transformer/pull/88/files
         * - Release
         */

        $transformedCollection = $this->resolveTransformedCollection();

        $this->outputTransformed($transformedCollection);

        if ($this->watch) {
            $watcher = new FileSystemWatcher(
                $this,
                $transformedCollection,
            );

            $watcher->run();
        }
    }

    public function resolveTransformedCollection(): TransformedCollection
    {
        $transformedCollection = $this->provideTypesAction->execute();

        $this->executeProvidedClosuresAction->execute($transformedCollection);

        $this->connectReferencesAction->execute($transformedCollection);

        $this->executeConnectedClosuresAction->execute($transformedCollection);

        return $transformedCollection;
    }

    public function outputTransformed(
        TransformedCollection $transformedCollection,
    ): void {
        if (! $transformedCollection->hasChanges()) {
            return;
        }

        $transformedCollection->rewriteExecuted();

        $writeableFiles = $this->config->writer->output($transformedCollection);

        $this->writeFilesAction->execute($writeableFiles);

        $this->formatFilesAction->execute($writeableFiles);
    }
}
