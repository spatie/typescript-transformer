<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\Laravel\Routes\InvokableRouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerCollection;
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
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptInterface;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptOperator;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;

// @todo implement the method, probably using a RawTypeScriptNode, creating individual notes for each JS construct is probably a bit far fetched
// @todo make sure we support __invoke routes without action
// @todo add support for nullable parameters, these should be inferred
// @todo a syntax like route(['Controller', 'action'], {params}), route(['Controller', 'action'], param), route(InvokeableController) would be even cooler but maybe too complicated at the moment

/**
 *     function route<
 * TController extends keyof Routes,
 * TAction extends keyof Routes[TController],
 * TParams extends Routes[TController][TAction]["parameters"]
 * >(action: [TController, TAction] | TController, params?: TParams): string {
 *
 * }
 */
class RouterGenerator implements DefaultTypesProvider
{
    public function provide(): array
    {
        $controllers = $this->resolveRoutes();

        $transformedRoutes = new Transformed(
            new TypeScriptAlias(
                new TypeScriptIdentifier('Routes'),
                $controllers->toTypeScriptNode(),
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
                    new TypeScriptIdentifier('route'),
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
                new TypeScriptRaw("let routes = JSON.parse('".json_encode($controllers->toJsObject(), flags: JSON_UNESCAPED_SLASHES). "')")
            ),
            null,
            'route',
            true,
            [],
        );

        return [$transformedRoutes, $actionParam, $transformedAction];
    }

    private function resolveRoutes(): RouteControllerCollection
    {
        /** @var array<string, RouteController> $controllers */
        $controllers = [];

        foreach (app(Router::class)->getRoutes()->getRoutes() as $route) {
            $controllerClass = $route->getControllerClass();

            if ($controllerClass === null) {
                continue;
            }

            $controllerClass = str_replace('\\', '.', $controllerClass);

            if ($route->getActionMethod() === $route->getControllerClass()) {
                $controllers[$controllerClass] = new InvokableRouteController(
                    $this->resolveRouteParameters($route),
                    $route->methods,
                    $this->resolveUrl($route),
                );

                continue;
            }

            if (! array_key_exists($controllerClass, $controllers)) {
                $controllers[$controllerClass] = new RouteController([]);
            }

            $controllers[$controllerClass]->actions[$route->getActionMethod()] = new RouteControllerAction(
                $route->getActionMethod(),
                $this->resolveRouteParameters($route),
                $route->methods,
                $this->resolveUrl($route),
            );
        }

        return new RouteControllerCollection($controllers);
    }

    protected function resolveRouteParameters(
        Route $route
    ): RouteParameterCollection {
        preg_match_all('/\{(.*?)\}/', $route->getDomain().$route->uri, $matches);

        $parameters = array_map(fn (string $match) => new RouteParameter(
            trim($match, '?'),
            str_ends_with($match, '?')
        ), $matches[1]);

        return new RouteParameterCollection($parameters);
    }

    protected function resolveUrl(Route $route): string
    {
        return str_replace('?}', '}', $route->getDomain().$route->uri);
    }
}
