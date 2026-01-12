<?php

namespace Spatie\TypeScriptTransformer\Laravel\RouteFilters;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ControllerRouteFilter implements RouteFilter
{
    /** @var array<string|array{0: class-string, 1: string}> */
    protected array $controllers;

    public function __construct(
        string|array ...$controllers
    ) {
        $this->controllers = $controllers;
    }

    public function hide(Route $route): bool
    {
        if ($route->getControllerClass() === null) {
            return false;
        }

        foreach ($this->controllers as $controller) {
            if (is_string($controller) && Str::is($controller, $route->getControllerClass())) {
                return true;
            }

            if (is_array($controller)
                && Str::is($controller[0], $route->getControllerClass())
                && Str::is($controller[1], $route->getActionMethod())
            ) {
                return true;
            }
        }

        return false;
    }
}
