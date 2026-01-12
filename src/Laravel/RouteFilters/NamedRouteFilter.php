<?php

namespace Spatie\TypeScriptTransformer\Laravel\RouteFilters;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class NamedRouteFilter implements RouteFilter
{
    /** @var array<string> */
    protected array $names;

    public function __construct(
        string ...$names
    ) {
        $this->names = $names;
    }

    public function hide(Route $route): bool
    {
        if ($route->getName() === null) {
            return false;
        }

        foreach ($this->names as $name) {
            if (Str::is($name, $route->getName())) {
                return true;
            }
        }

        return false;
    }
}
