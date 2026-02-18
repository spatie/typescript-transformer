<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\CollectAdditionalImportsAction;
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
use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\PhpClassReference;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Loggers\Logger;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProviderActions;

class TypeScriptTransformer
{
    public function __construct(
        public readonly TypeScriptTransformerConfig $config,
        public readonly Logger $logger,
        public readonly DiscoverTypesAction $discoverTypesAction,
        public readonly RunProvidersAction $runProvidersAction,
        public readonly ExecuteProvidedClosuresAction $executeProvidedClosuresAction,
        public readonly ConnectReferencesAction $connectReferencesAction,
        public readonly CollectAdditionalImportsAction $collectAdditionalImportsAction,
        public readonly ExecuteConnectedClosuresAction $executeConnectedClosuresAction,
        public readonly ResolveFilesAction $resolveFilesAction,
        public readonly WriteFilesAction $writeFilesAction,
        public readonly FormatFilesAction $formatFilesAction,
        public readonly TransformTypesAction $transformTypesAction,
        public readonly LoadPhpClassNodeAction $loadPhpClassNodeAction,
        public readonly TransformedProviderActions $transformedProviderActions,
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

        $transformTypesAction = new TransformTypesAction();
        $loadPhpClassNodeAction = new LoadPhpClassNodeAction();

        return new self(
            $config,
            $logger,
            $discoverTypesAction = new DiscoverTypesAction(),
            new RunProvidersAction($config),
            new ExecuteProvidedClosuresAction($config),
            new ConnectReferencesAction($logger),
            new CollectAdditionalImportsAction($config),
            new ExecuteConnectedClosuresAction($config),
            new ResolveFilesAction($config),
            new WriteFilesAction($config),
            new FormatFilesAction($config),
            $transformTypesAction,
            $loadPhpClassNodeAction,
            new TransformedProviderActions(
                loadPhpClassNodeAction: $loadPhpClassNodeAction,
                discoverTypesAction: $discoverTypesAction,
            ),
            $watch
        );
    }

    public function execute(): void
    {
        [$transformedCollection, $phpNodeCollection] = $this->resolveState();

        $this->outputTransformed($transformedCollection);

        if ($this->watch) {
            $this->signalWorkerReady();

            $watcher = new FileSystemWatcher(
                $this,
                $transformedCollection,
                $phpNodeCollection,
            );

            $watcher->run();
        }
    }

    protected function signalWorkerReady(): void
    {
        $this->logger->info(Runner::WORKER_READY_SIGNAL);
    }

    /**
     * @return array{TransformedCollection, PhpNodeCollection}
     */
    public function resolveState(): array
    {
        $phpNodeCollection = new PhpNodeCollection();

        $transformedCollection = $this->runProvidersAction->execute($this->logger, $phpNodeCollection, $this->transformedProviderActions);

        foreach ($transformedCollection as $transformed) {
            if ($transformed->reference instanceof PhpClassReference) {
                $phpNodeCollection->add($transformed->reference->phpClassNode);
            }
        }

        $this->executeProvidedClosuresAction->execute($transformedCollection);

        $this->connectReferencesAction->execute($transformedCollection);

        $this->collectAdditionalImportsAction->execute($transformedCollection);

        $this->executeConnectedClosuresAction->execute($transformedCollection);

        return [$transformedCollection, $phpNodeCollection];
    }

    public function outputTransformed(TransformedCollection $transformedCollection): void
    {
        if (! $transformedCollection->hasChanges()) {
            return;
        }

        $transformedCollection->rewriteExecuted();

        $writeableFiles = $this->resolveFilesAction->execute($transformedCollection);

        $this->writeFilesAction->execute($writeableFiles);

        $this->formatFilesAction->execute($writeableFiles);
    }
}
