<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRoutControllerCollectionsAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerCollection;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteInvokableController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameterCollection;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
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

// @todo implement the method, probably using a RawTypeScriptNode, creating individual notes for each JS construct is probably a bit far fetched
// @todo make sure we support __invoke routes without action
// @todo add support for nullable parameters, these should be inferred

/**
 *     function route<
 * TController extends keyof Routes,
 * TAction extends keyof Routes[TController],
 * TParams extends Routes[TController][TAction]["parameters"]
 * >(action: [TController, TAction] | TController, params?: TParams): string {
 *
 * }
 */
class LaravelActionDefaultTypesProvider implements DefaultTypesProvider
{
    public function __construct(
        protected ResolveLaravelRoutControllerCollectionsAction $resolveLaravelRoutControllerCollectionsAction = new ResolveLaravelRoutControllerCollectionsAction()
    ) {
    }

    public function provide(): array
    {
        $controllers = $this->resolveLaravelRoutControllerCollectionsAction->execute();

        $transformedRoutes = new Transformed(
            new TypeScriptAlias(
                new TypeScriptIdentifier('Routes'),
                $this->parseRouteControllerCollection($controllers),
            ),
            null,
            'Routes',
            true,
            [],
        );

        $actionParam = new Transformed(
            new TypeScriptAlias(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('ActionParam'),
                    [
                        new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TController')),
                        new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TAction')),
                    ]
                ),
                new TypeScriptConditional(
                    TypeScriptOperator::extends(
                        new TypeScriptIndexedAccess(
                            new TypeScriptIdentifier('Routes'),
                            [new TypeScriptIdentifier('TController')],
                        ),
                        new TypeScriptObject([
                            new TypeScriptProperty('invokable', new TypeScriptRaw('true')),
                        ])
                    ),
                    new TypeScriptIdentifier('TController'),
                    new TypeScriptArray([
                        new TypeScriptIdentifier('TController'),
                        new TypeScriptIdentifier('TAction'),
                    ])
                )
            ),
            null,
            'ActionParam',
            true,
            [],
        );

        $transformedAction = new Transformed(
            new TypeScriptFunctionDefinition(
                new TypeScriptGeneric(
                    new TypeScriptIdentifier('action'),
                    [
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TController'),
                            extends: new TypeScriptIdentifier('keyof Routes'),
                        ),
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TAction'),
                            extends: new TypeScriptIdentifier('keyof Routes[TController]["actions"]'),
                        ),
                        new TypeScriptGenericTypeVariable(
                            new TypeScriptIdentifier('TParams'),
                            extends: new TypeScriptIdentifier('Routes[TController]["actions"][TAction]["parameters"]'),
                        ),
                    ]
                ),
                [
                    new TypeScriptParameter('action', new TypeScriptGeneric(
                        new TypeScriptIdentifier('ActionParam'),
                        [
                            new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TController')),
                            new TypeScriptGenericTypeVariable(new TypeScriptIdentifier('TAction')),
                        ]
                    )),
                    new TypeScriptParameter('params', new TypeScriptIdentifier('TParams'), isOptional: true),
                ],
                new TypeScriptString(),
                new TypeScriptRaw("let routes = JSON.parse('".json_encode($controllers->toJsObject(), flags: JSON_UNESCAPED_SLASHES)."')")
            ),
            null,
            'action',
            true,
            [],
        );

        return [$transformedRoutes, $actionParam, $transformedAction];
    }

    protected function parseRouteControllerCollection(RouteControllerCollection $collection): TypeScriptNode
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
