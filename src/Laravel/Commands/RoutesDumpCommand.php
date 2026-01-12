<?php

namespace Spatie\TypeScriptTransformer\Laravel\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRouteControllerCollectionsAction;

class RoutesDumpCommand extends Command
{
    public $signature = 'typescript:dump-routes {defaultNamespace} {filters} {--include-route-closures}';

    public $description = 'Transforms Laravel route definitions to TypeScript Transformer usable format.';

    protected $hidden = true;

    public function handle(
        ResolveLaravelRouteControllerCollectionsAction $resolveLaravelRouteControllerCollectionsAction
    ): int {
        $defaultNamespace = $this->argument('defaultNamespace');

        if ($defaultNamespace === 'null') {
            $defaultNamespace = null;
        }

        $filters = $this->argument('filters');

        if ($filters === 'null') {
            $filters = null;
        }

        $routeCollection = $resolveLaravelRouteControllerCollectionsAction->execute(
            defaultNamespace: $defaultNamespace,
            includeRouteClosures: $this->option('include-route-closures'),
            filters: $filters === null ? [] : unserialize($filters)
        );

        $this->output->write(serialize($routeCollection));

        return self::SUCCESS;
    }
}
