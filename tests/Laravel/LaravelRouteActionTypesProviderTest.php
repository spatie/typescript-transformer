<?php

use Spatie\TypeScriptTransformer\Laravel\LaravelRouteActionTypesProvider;

it('provides the correct route action types', function () {
    $provider = new LaravelRouteActionTypesProvider();

    $route = new Route('GET', 'test', 'TestController@test');

    $actionTypes = $provider->getActionTypes($route);

    expect($actionTypes)->toBe([
        'controller' => 'TestController',
        'method' => 'test',
    ]);
});
