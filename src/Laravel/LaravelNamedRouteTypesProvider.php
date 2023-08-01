<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRoutControllerCollectionsAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteClosure;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteCollection;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteInvokableController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameterCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptFunctionDefinition;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGenericTypeVariable;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIndexedAccess;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class LaravelNamedRouteTypesProvider implements TypesProvider
{
    public function __construct(
        protected ResolveLaravelRoutControllerCollectionsAction $resolveLaravelRoutControllerCollectionsAction = new ResolveLaravelRoutControllerCollectionsAction(),
        protected array $location = [],
    ) {
    }

    public function provide(TypeScriptTransformerConfig $config, TypeScriptTransformerLog $log, TransformedCollection $types): void
    {
        $routeCollection = $this->resolveLaravelRoutControllerCollectionsAction->execute(
            null,
            includeRouteClosures: true,
        );

        $transformedRoutes = new Transformed(
            new TypeScriptAlias(
                new TypeScriptIdentifier('NamedRouteList'),
                $this->parseRouteCollection($routeCollection),
            ),
            $routesListReference = new CustomReference('laravel_named_routes', 'routes_list'),
            'NamedRouteList',
            $this->location,
        );

        $jsonEncodedRoutes = $this->routeCollectionToJson($routeCollection);
        $baseUrl = url('/');

        $transformedRoute = new Transformed(
            new TypeScriptFunctionDefinition(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('route'),
                    [
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TRoute'),
                            extends: TypeScriptOperator::keyof(new TypeReference($routesListReference))
                        ),
                    ]
                ),
                [
                    new TypeScriptParameter('route', new TypeScriptIdentifier('TRoute')),
                    new TypeScriptParameter(
                        'parameters',
                        new TypeScriptIndexedAccess(new TypeReference($routesListReference), [
                            new TypeScriptIdentifier('TRoute'),
                            new TypeScriptIdentifier('"parameters"'),
                        ]),
                        isOptional: true),
                ],
                new TypeScriptString(),
                new TypeScriptRaw(<<<TS
let routes = JSON.parse('$jsonEncodedRoutes');
let baseUrl = '$baseUrl';

let found = routes[route];

let url = baseUrl + '/' + found.url;

if(parameters) {
    for(let parameter in parameters) {
        url = url.replace('{' + parameter + '}', parameters[parameter]);
    }
}

return url;
TS
                )
            ),
            new CustomReference('laravel_named_routes', 'route_function'),
            'route',
            $this->location,
        );

        $types->add($transformedRoutes, $transformedRoute);
    }

    protected function parseRouteCollection(RouteCollection $collection): TypeScriptNode
    {
        $mappingFunction = fn (RouteControllerAction|RouteInvokableController|RouteClosure $entity) => new TypeScriptProperty(
            $entity->name,
            new TypeScriptObject([
                new TypeScriptProperty(
                    'parameters',
                    $this->parseRouteParameterCollection($entity->parameters),
                ),
            ])
        );

        $properties = collect(array_merge($collection->controllers, $collection->closures))
            ->flatMap(function (RouteController|RouteInvokableController|RouteClosure $entity) use ($mappingFunction) {
                $singleRoute = $entity instanceof RouteInvokableController || $entity instanceof RouteClosure;

                if ($singleRoute && $entity->name) {
                    return [$mappingFunction($entity)];
                }

                if ($entity instanceof RouteController) {
                    return collect($entity->actions)
                        ->filter(fn (RouteControllerAction $action) => $action->name)
                        ->values()
                        ->map($mappingFunction);
                }

                return [];
            })
            ->all();

        return new TypeScriptObject($properties);
    }

    protected function parseRouteParameterCollection(RouteParameterCollection $collection): TypeScriptNode
    {
        return new TypeScriptObject(array_map(function (RouteParameter $parameter) {
            return $this->parseRouteParameter($parameter);
        }, $collection->parameters));
    }

    protected function parseRouteParameter(RouteParameter $parameter): TypeScriptNode
    {
        return new TypeScriptProperty(
            $parameter->name,
            new TypeScriptUnion([new TypeScriptString(), new TypeScriptNumber()]),
            isOptional: $parameter->optional,
        );
    }

    protected function routeCollectionToJson(RouteCollection $collection): string
    {
        $mappingFunction = fn (RouteInvokableController|RouteControllerAction|RouteClosure $entity) => [
            $entity->name => [
                'url' => $entity->url,
                'methods' => array_values($entity->methods),
            ],
        ];

        $controllers = collect($collection->controllers)->mapWithKeys(function (RouteController|RouteInvokableController $controller) use ($mappingFunction) {
            if ($controller instanceof RouteInvokableController && $controller->name) {
                return $mappingFunction($controller);
            }

            if ($controller instanceof RouteController) {
                return collect($controller->actions)
                    ->filter(fn (RouteControllerAction $action) => $action->name)
                    ->values()
                    ->mapWithKeys($mappingFunction);
            }

            return [];
        });

        $closures = collect($collection->closures)
            ->filter(fn (RouteClosure $closure) => $closure->name)
            ->values()
            ->mapWithKeys(function (RouteClosure $closure) use ($mappingFunction) {
                return $mappingFunction($closure);
            });

        return $controllers->merge($closures)->toJson(JSON_UNESCAPED_SLASHES);
    }
}
