<?php

namespace Spatie\TypeScriptTransformer\Laravel\RouteFilters;

use Illuminate\Routing\Route;

interface RouteFilter
{
    public function hide(Route $route): bool;
}
