<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\ExecuteConnectedClosuresAction;
use Spatie\TypeScriptTransformer\Actions\ExecuteProvidedClosuresAction;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Actions\LoadPhpClassNodeAction;
use Spatie\TypeScriptTransformer\Actions\ResolveFilesAction;
use Spatie\TypeScriptTransformer\Actions\RunProvidersAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Actions\WriteFilesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Collections\WritersCollection;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;

class TypeScriptTransformer
{
    public function __construct(
        public readonly TypeScriptTransformerConfig $config,
        public readonly Logger $logger,
        public readonly DiscoverTypesAction $discoverTypesAction,
        public readonly RunProvidersAction $runProvidersAction,
        public readonly ExecuteProvidedClosuresAction $executeProvidedClosuresAction,
        public readonly ConnectReferencesAction $connectReferencesAction,
        public readonly ExecuteConnectedClosuresAction $executeConnectedClosuresAction,
        public readonly ResolveFilesAction $resolveFilesAction,
        public readonly WriteFilesAction $writeFilesAction,
        public readonly FormatFilesAction $formatFilesAction,
        public readonly TransformTypesAction $transformTypesAction,
        public readonly LoadPhpClassNodeAction $loadPhpClassNodeAction,
        public readonly bool $watch = false,
    ) {

    }

    public static function create(
        TypeScriptTransformerConfig|TypeScriptTransformerConfigFactory $config,
        ?Logger $logger = null,
        bool $watch = false,
    ): self {
        $config = $config instanceof TypeScriptTransformerConfigFactory ? $config->get() : $config;

        $logger ??= new NullLogger();

        return new self(
            $config,
            $logger,
            new DiscoverTypesAction(),
            new RunProvidersAction($config),
            new ExecuteProvidedClosuresAction($config),
            new ConnectReferencesAction($logger),
            new ExecuteConnectedClosuresAction($config),
            new ResolveFilesAction(),
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
         * Laravel
         * - Check old Laravel tests if we missed something
         * - Further write docs + check them -> check TODO's
         * - Test Laravel specific stuff
         *
         *- Fix
         * - Release
         */

        [$transformedCollection, $writersCollection] = $this->resolveState();

        $this->outputTransformed($transformedCollection, $writersCollection);

        if ($this->watch) {
            $this->signalWorkerReady();

            $watcher = new FileSystemWatcher(
                $this,
                $transformedCollection,
                $writersCollection,
            );

            $watcher->run();
        }
    }

    protected function signalWorkerReady(): void
    {
        $this->logger->info(Runner::WORKER_READY_SIGNAL);
    }

    /**
     * @return array{TransformedCollection, WritersCollection}
     */
    public function resolveState(): array
    {
        [$transformedCollection, $writersCollection] = $this->runProvidersAction->execute($this->logger);

        $this->executeProvidedClosuresAction->execute($transformedCollection);

        $this->connectReferencesAction->execute($transformedCollection);

        $this->executeConnectedClosuresAction->execute($transformedCollection);

        return [$transformedCollection, $writersCollection];
    }

    public function outputTransformed(
        TransformedCollection $transformedCollection,
        WritersCollection $writersCollection,
    ): void {
        if (! $transformedCollection->hasChanges()) {
            return;
        }

        $transformedCollection->rewriteExecuted();

        $writeableFiles = $this->resolveFilesAction->execute($transformedCollection, $writersCollection);

        $this->writeFilesAction->execute($writeableFiles);

        $this->formatFilesAction->execute($writeableFiles);
    }
}
