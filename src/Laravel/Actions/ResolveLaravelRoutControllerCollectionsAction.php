<?php

namespace Spatie\TypeScriptTransformer\Laravel\Actions;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteInvokableController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerCollection;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameterCollection;

class ResolveLaravelRoutControllerCollectionsAction
{
    public function execute(): RouteControllerCollection
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
                $controllers[$controllerClass] = new RouteInvokableController(
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
