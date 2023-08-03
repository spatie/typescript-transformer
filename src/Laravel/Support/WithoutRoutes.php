<?php

namespace Spatie\TypeScriptTransformer\Laravel\Support;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class WithoutRoutes
{
    protected function __construct(protected Closure $closure)
    {
    }

    public function shouldHide(Route $route): bool
    {
        return ($this->closure)($route);
    }

    public static function satisfying(Closure $closure): self
    {
        return new static($closure);
    }

    public static function named(string ...$names): self
    {
        return new self(function (Route $route) use ($names): bool {
            if ($route->getName() === null) {
                return false;
            }

            foreach ($names as $name) {
                if (Str::is($name, $route->getName())) {
                    return true;
                }
            }

            return false;
        });
    }

    public static function controller(string|array ...$controllers): self
    {
        return new self(function (Route $route) use ($controllers): bool {
            if ($route->getControllerClass() === null) {
                return false;
            }

            foreach ($controllers as $controller) {
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
        });
    }
}
