<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRoutControllerCollectionsAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteCollection;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteInvokableController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameterCollection;
use Spatie\TypeScriptTransformer\Laravel\Support\WithoutRoutes;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptConditional;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptFunctionDefinition;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGenericTypeVariable;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIndexedAccess;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class LaravelRouteActionTypesProvider implements TypesProvider
{
    /**
     * @param  array<string>  $location
     * @param  array<WithoutRoutes>  $filters
     */
    public function __construct(
        protected ResolveLaravelRoutControllerCollectionsAction $resolveLaravelRoutControllerCollectionsAction = new ResolveLaravelRoutControllerCollectionsAction(),
        protected ?string $defaultNamespace = null,
        protected array $location = ['App'],
        protected array $filters = [],
    ) {
    }

    public function provide(TypeScriptTransformerConfig $config, TransformedCollection $types): void
    {
        $routeCollection = $this->resolveLaravelRoutControllerCollectionsAction->execute(
            defaultNamespace: $this->defaultNamespace,
            includeRouteClosures: false,
            filters: $this->filters,
        );

        $transformedRoutes = new Transformed(
            new TypeScriptAlias(
                new TypeScriptIdentifier('ActionRoutesList'),
                $this->parseRouteCollection($routeCollection),
            ),
            $routesListReference = new CustomReference('laravel_route_actions', 'routes_list'),
            $this->location,
            true,
        );

        $isInvokableControllerCondition = TypeScriptOperator::extends(
            new TypeScriptIndexedAccess(
                new TypeReference($routesListReference),
                [new TypeScriptIdentifier('TController')],
            ),
            new TypeScriptObject([
                new TypeScriptProperty('invokable', new TypeScriptRaw('true')),
            ])
        );

        $actionController = new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('ActionController'),
                    [
                        new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TController')),
                        new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TAction')),
                    ]
                ),
                new TypeScriptConditional(
                    $isInvokableControllerCondition,
                    new TypeScriptIdentifier('TController'),
                    new TypeScriptArray([
                        new TypeScriptIdentifier('TController'),
                        new TypeScriptIdentifier('TAction'),
                    ])
                )
            ),
            $actionControllerReference = new CustomReference('laravel_route_actions', 'action_controller'),
            $this->location,
            true,
        );

        $actionParameters = new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('ActionParameters'),
                    [
                        new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TController')),
                        new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TAction')),
                    ]
                ),
                new TypeScriptConditional(
                    $isInvokableControllerCondition,
                    new TypeScriptIndexedAccess(new TypeReference($routesListReference), [
                        new TypeScriptIdentifier('TController'),
                        new TypeScriptIdentifier('"parameters"'),
                    ]),
                    new TypeScriptIndexedAccess(new TypeReference($routesListReference), [
                        new TypeScriptIdentifier('TController'),
                        new TypeScriptIdentifier('"actions"'),
                        new TypeScriptIdentifier('TAction'),
                        new TypeScriptIdentifier('"parameters"'),
                    ])
                )
            ),
            $actionParametersReference = new CustomReference('laravel_route_actions', 'action_parameters'),
            $this->location,
            true,
        );

        $jsonEncodedRoutes = $this->routeCollectionToJson($routeCollection);
        $baseUrl = url('/');

        $transformedAction = new Transformed(
            new TypeScriptFunctionDefinition(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('action'),
                    [
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TController'),
                            extends: TypeScriptOperator::keyof(new TypeReference($routesListReference))
                        ),
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TAction'),
                            extends: TypeScriptOperator::keyof(new TypeScriptIndexedAccess(new TypeReference($routesListReference), [
                                new TypeScriptIdentifier('TController'),
                                new TypeScriptIdentifier('"actions"'),
                            ]))
                        ),
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TParams'),
                            extends: new TypeScriptIndexedAccess(new TypeReference($routesListReference), [
                                new TypeScriptIdentifier('TController'),
                                new TypeScriptIdentifier('"actions"'),
                                new TypeScriptIdentifier('TAction'),
                                new TypeScriptIdentifier('"parameters"'),
                            ])
                        ),
                    ]
                ),
                [
                    new TypeScriptParameter('action', new TypeScriptGeneric(
                        new TypeReference($actionControllerReference),
                        [
                            new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TController')),
                            new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TAction')),
                        ]
                    )),
                    new TypeScriptParameter('parameters', new TypeScriptGeneric(
                        new TypeReference($actionParametersReference),
                        [
                            new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TController')),
                            new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TAction')),
                        ]
                    ), isOptional: true),
                ],
                new TypeScriptString(),
                new TypeScriptRaw(
                    <<<TS
let routes = JSON.parse('$jsonEncodedRoutes');
let baseUrl = '$baseUrl';

let found = typeof action === 'string'
    ? routes[action]
    : routes[action[0]]['actions'][action[1]];

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
            new CustomReference('laravel_route_actions', 'action_function'),
            $this->location,
            true,
        );

        $types->add($transformedRoutes, $actionController, $actionParameters, $transformedAction);
    }

    protected function parseRouteCollection(RouteCollection $collection): TypeScriptNode
    {
        return new TypeScriptObject(collect($collection->controllers)->map(function (RouteController|RouteInvokableController $controller, string $name) {
            return new TypeScriptProperty(
                $name,
                $controller instanceof RouteInvokableController
                    ? $this->parseInvokableController($controller)
                    : $this->parseController($controller),
            );
        })->all());
    }

    protected function parseController(RouteController $controller): TypeScriptNode
    {
        return new TypeScriptObject([
            new TypeScriptProperty('actions', new TypeScriptObject(collect($controller->actions)->map(function (RouteControllerAction $action, string $name) {
                return new TypeScriptProperty(
                    $name,
                    $this->parseControllerAction($action)
                );
            })->all())),
        ]);
    }

    protected function parseControllerAction(RouteControllerAction $action): TypeScriptNode
    {
        return new TypeScriptObject([
            new TypeScriptProperty('parameters', $this->parseRouteParameterCollection($action->parameters)),
        ]);
    }

    protected function parseInvokableController(RouteInvokableController $controller): TypeScriptNode
    {
        return new TypeScriptObject([
            new TypeScriptProperty('invokable', new TypeScriptRaw('true')),
            new TypeScriptProperty('parameters', $this->parseRouteParameterCollection($controller->parameters)),
        ]);
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
        return collect($collection->controllers)
            ->map(
                fn (RouteController|RouteInvokableController $controller) => $controller instanceof RouteInvokableController
                ? [
                    'url' => $controller->url,
                    'methods' => array_values($controller->methods),
                ]
                : [
                    'actions' => collect($controller->actions)->map(fn (RouteControllerAction $action) => [
                        'url' => $action->url,
                        'methods' => array_values($action->methods),
                    ]),
                ]
            )
            ->toJson(JSON_UNESCAPED_SLASHES);
    }
}
