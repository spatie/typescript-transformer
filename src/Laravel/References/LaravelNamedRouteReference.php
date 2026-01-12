<?php

namespace Spatie\TypeScriptTransformer\Laravel\References;

class LaravelNamedRouteReference extends LaravelRouteReference
{
    protected function getKind(): string
    {
        return 'laravel-named-route';
    }
}
