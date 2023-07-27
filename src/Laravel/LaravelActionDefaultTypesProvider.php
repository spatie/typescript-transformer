<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRoutControllerCollectionsAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteCollection;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteInvokableController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameterCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptConditional;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptFunctionDefinition;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGenericTypeVariable;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIndexedAccess;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class LaravelActionDefaultTypesProvider implements DefaultTypesProvider
{
    public function __construct(
        protected ResolveLaravelRoutControllerCollectionsAction $resolveLaravelRoutControllerCollectionsAction = new ResolveLaravelRoutControllerCollectionsAction(),
        protected ?string $defaultNamespace = null,
        protected array $location = [],
    ) {
    }

    public function provide(): array
    {
        $controllers = $this->resolveLaravelRoutControllerCollectionsAction->execute(
            $this->defaultNamespace,
            includeRouteClosures: false,
        );

        $transformedRoutes = new Transformed(
            new TypeScriptAlias(
                new TypeScriptIdentifier('RoutesList'),
                $this->parseRouteControllerCollection($controllers),
            ),
            $routesListReference = new CustomReference('laravel_route_actions', 'routes_list'),
            'RoutesList',
            true,
            $this->location,
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
            'ActionController',
            true,
            $this->location,
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
            'ActionParameters',
            true,
            $this->location,
        );

        $jsonEncodedRoutes = json_encode($controllers->toJsObject(), flags: JSON_UNESCAPED_SLASHES);
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
                new TypeScriptRaw(<<<TS
let routes = JSON.parse('$jsonEncodedRoutes');
let baseUrl = '$baseUrl';

let found = typeof action === 'string'
    ? routes.controllers[action]
    : routes.controllers[action[0]]['actions'][action[1]];

let url = baseUrl + '/' + found.url;

if(parameters) {
    for(let parameter in parameters) {
        url = url.replace('{' + parameter + '}', parameters[parameter]);
    }
}

return url;
TS
                )
                //                new TypeScriptRaw("let routes = JSON.parse('".json_encode($controllers->toJsObject(), flags: JSON_UNESCAPED_SLASHES)."')")
            ),
            new CustomReference('laravel_route_actions', 'action_function'),
            'action',
            true,
            $this->location,
        );

        return [$transformedRoutes, $actionController, $actionParameters, $transformedAction];
    }

    protected function parseRouteControllerCollection(RouteCollection $collection): TypeScriptNode
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
            new TypeScriptProperty('name', new TypeScriptLiteral($action->name)),
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
}
