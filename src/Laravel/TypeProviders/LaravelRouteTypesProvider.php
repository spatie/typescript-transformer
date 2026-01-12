<?php

namespace Spatie\TypeScriptTransformer\Laravel\TypeProviders;

use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Facades\Process;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\Watch\SummarizedWatchEvent;
use Spatie\TypeScriptTransformer\Events\Watch\WatchEvent;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRouteControllerCollectionsAction;
use Spatie\TypeScriptTransformer\Laravel\RouteFilters\RouteFilter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteCollection;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeProviders\LoggingTypesProvider;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeProviders\WatchingTypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

abstract class LaravelRouteTypesProvider implements TypesProvider, WatchingTypesProvider, LoggingTypesProvider
{
    protected ?string $routeCollectionHash = null;

    protected Logger $logger;

    /**
     * @param array<RouteFilter> $filters
     */
    public function __construct(
        protected ResolveLaravelRouteControllerCollectionsAction $resolveLaravelRoutControllerCollectionsAction,
        protected ?string $defaultNamespace,
        protected bool $includeRouteClosures,
        protected array $filters,
    ) {
    }

    public function directoriesToWatch(): array
    {
        return [];
    }

    public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
    {
        $routeCollection = $this->resolveLaravelRoutControllerCollectionsAction->execute(
            defaultNamespace: $this->defaultNamespace,
            includeRouteClosures: $this->includeRouteClosures,
            filters: $this->filters,
        );

        $this->routeCollectionHash = md5(serialize($routeCollection));

        $types->add(...$this->resolveTransformed($routeCollection));
    }

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): int|WatchEventResult
    {
        if (! $watchEvent instanceof SummarizedWatchEvent) {
            return WatchEventResult::continue();
        }

        $commandParts = [
            'php',
            'artisan',
            'typescript:dump-routes',
            $this->defaultNamespace ?? 'null',
            $this->filters ? serialize($this->filters) : 'null',
            $this->includeRouteClosures ? '--include-route-closures' : '',
        ];

        try {
            $serialized = Process::timeout(2)
                ->run($commandParts)
                ->throw()
                ->output();
        } catch (ProcessTimedOutException) {
            $this->logger->error('The nested command to dump the routes collection timed out.');

            return WatchEventResult::continue();
        } catch (ProcessFailedException) {
            $this->logger->error('The nested command to dump the routes collection failed.');

            return WatchEventResult::continue();
        }

        if ($this->routeCollectionHash && $this->routeCollectionHash === md5($serialized)) {
            return WatchEventResult::continue();
        }

        try {
            $routesCollection = unserialize($serialized);
        } catch (\Throwable $e) {
            $this->logger->error('Could not unserialize the routes collection from the nested command.');

            return WatchEventResult::continue();
        }

        $transformedEntities = $this->resolveTransformed($routesCollection);

        foreach ($transformedEntities as $transformed) {
            $transformedCollection->remove($transformed->reference);
        }

        $transformedCollection->add(...$transformedEntities);

        return WatchEventResult::continue();
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /** @return array<Transformed> */
    abstract protected function resolveTransformed(RouteCollection $routeCollection): array;
}
