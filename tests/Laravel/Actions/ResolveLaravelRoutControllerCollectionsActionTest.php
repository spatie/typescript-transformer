<?php

use Illuminate\Routing\Router;
use Spatie\TypeScriptTransformer\Laravel\Actions\ResolveLaravelRouteControllerCollectionsAction;
use Spatie\TypeScriptTransformer\Laravel\RouteFilters\ControllerRouteFilter;
use Spatie\TypeScriptTransformer\Laravel\RouteFilters\NamedRouteFilter;
use Spatie\TypeScriptTransformer\Laravel\RouteFilters\RouteFilter;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteCollection;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteControllerAction;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteInvokableController;
use Spatie\TypeScriptTransformer\Laravel\Routes\RouteParameterCollection;
use Spatie\TypeScriptTransformer\Tests\Laravel\FakeClasses\InvokableController;
use Spatie\TypeScriptTransformer\Tests\Laravel\FakeClasses\ResourceController;
use Spatie\TypeScriptTransformer\Tests\Laravel\LaravelTestCase;
use Symfony\Component\HttpKernel\Controller\ErrorController;

uses(LaravelTestCase::class)->in(__DIR__.'/../');

it('can resolve all possible routes', function (Closure $route, Closure $expectations) {
    $router = app(Router::class);

    $router->setRoutes(new \Illuminate\Routing\RouteCollection()); // Laravel registers a storage.local route by default, which we want to ignore in this test.

    $route($router);

    $routes = app(ResolveLaravelRouteControllerCollectionsAction::class)->execute(null, true);

    $expectations($routes);
})->with(function () {
    yield 'simple closure' => [
        fn (Router $router) => $router->get('simple', fn () => 'simple'),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toBeEmpty();
            expect($routes->closures)->toHaveCount(1);

            expect($routes->closures['Closure(simple)']->url)->toBe('simple');
            expect($routes->closures['Closure(simple)']->methods)->toBe(['GET', 'HEAD']);
        },
    ];
    yield 'controller action' => [
        fn (Router $router) => $router->get('action', [ResourceController::class, 'update']),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toHaveCount(1);
            expect($routes->closures)->toBeEmpty();

            $actions = $routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController']->actions;

            expect($actions)->toHaveCount(1);
            expect($actions['update'])->toBeInstanceOf(RouteControllerAction::class);
            expect($actions['update']->url)->toBe('action');
            expect($actions['update']->methods)->toBe(['GET', 'HEAD']);

            expect($actions['update']->parameters)->toBeInstanceOf(RouteParameterCollection::class);
            expect($actions['update']->parameters->parameters)->toBeEmpty();
        },
    ];
    yield 'invokable controller' => [
        fn (Router $router) => $router->get('invokable', InvokableController::class),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toHaveCount(1);
            expect($routes->closures)->toBeEmpty();

            $controller = $routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.InvokableController'];

            expect($controller)->toBeInstanceOf(RouteInvokableController::class);
            expect($controller->url)->toBe('invokable');
            expect($controller->methods)->toBe(['GET', 'HEAD']);

            expect($controller->parameters)->toBeInstanceOf(RouteParameterCollection::class);
            expect($controller->parameters->parameters)->toBeEmpty();
        },
    ];
    yield 'resource controller' => [
        fn (Router $router) => $router->resource('resource', ResourceController::class),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toHaveCount(1);
            expect($routes->closures)->toBeEmpty();

            $controller = $routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController'];

            expect($controller)->toBeInstanceOf(RouteController::class);
            expect($controller->actions)->toHaveCount(7);

            expect($controller->actions['index'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['index']->url)->toBe('resource');
            expect($controller->actions['index']->methods)->toBe(['GET', 'HEAD']);
            expect($controller->actions['index']->parameters->parameters)->toBeEmpty();

            expect($controller->actions['create'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['create']->url)->toBe('resource/create');
            expect($controller->actions['create']->methods)->toBe(['GET', 'HEAD']);
            expect($controller->actions['create']->parameters->parameters)->toBeEmpty();

            expect($controller->actions['store'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['store']->url)->toBe('resource');
            expect($controller->actions['store']->methods)->toBe(['POST']);
            expect($controller->actions['store']->parameters->parameters)->toBeEmpty();

            expect($controller->actions['show'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['show']->url)->toBe('resource/{resource}');
            expect($controller->actions['show']->methods)->toBe(['GET', 'HEAD']);
            expect($controller->actions['show']->parameters->parameters)->toHaveCount(1);

            expect($controller->actions['edit'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['edit']->url)->toBe('resource/{resource}/edit');
            expect($controller->actions['edit']->methods)->toBe(['GET', 'HEAD']);
            expect($controller->actions['edit']->parameters->parameters)->toHaveCount(1);

            expect($controller->actions['update'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['update']->url)->toBe('resource/{resource}');
            expect($controller->actions['update']->methods)->toBe(['PUT', 'PATCH']);
            expect($controller->actions['update']->parameters->parameters)->toHaveCount(1);

            expect($controller->actions['destroy'])->toBeInstanceOf(RouteControllerAction::class);
            expect($controller->actions['destroy']->url)->toBe('resource/{resource}');
            expect($controller->actions['destroy']->methods)->toBe(['DELETE']);
            expect($controller->actions['destroy']->parameters->parameters)->toHaveCount(1);
        },
    ];
    yield 'nested' => [
        fn (Router $router) => $router->group(['prefix' => 'nested'], fn (Router $router) => $router->get('simple', fn () => 'simple')),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toBeEmpty();
            expect($routes->closures)->toHaveCount(1);

            expect($routes->closures['Closure(nested/simple)']->url)->toBe('nested/simple');
            expect($routes->closures['Closure(nested/simple)']->methods)->toBe(['GET', 'HEAD']);
        },
    ];
    yield 'methods' => [
        function (Router $router) {
            $router->get('get', fn () => 'get');
            $router->post('post', fn () => 'post');
            $router->put('put', fn () => 'put');
            $router->patch('patch', fn () => 'patch');
            $router->delete('delete', fn () => 'delete');
            $router->options('options', fn () => 'options');
        },
        function (RouteCollection $routes) {
            expect($routes->controllers)->toBeEmpty();
            expect($routes->closures)->toHaveCount(6);

            expect($routes->closures['Closure(get)']->methods)->toBe(['GET', 'HEAD']);
            expect($routes->closures['Closure(post)']->methods)->toBe(['POST']);
            expect($routes->closures['Closure(put)']->methods)->toBe(['PUT']);
            expect($routes->closures['Closure(patch)']->methods)->toBe(['PATCH']);
            expect($routes->closures['Closure(delete)']->methods)->toBe(['DELETE']);
            expect($routes->closures['Closure(options)']->methods)->toBe(['OPTIONS']);
        },
    ];
    yield 'parameter' => [
        fn (Router $router) => $router->get('simple/{id}', fn () => 'simple'),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toBeEmpty();
            expect($routes->closures)->toHaveCount(1);

            expect($routes->closures['Closure(simple/{id})']->url)->toBe('simple/{id}');
            expect($routes->closures['Closure(simple/{id})']->methods)->toBe(['GET', 'HEAD']);
            expect($routes->closures['Closure(simple/{id})']->parameters->parameters)->toHaveCount(1);
            expect($routes->closures['Closure(simple/{id})']->parameters->parameters[0]->name)->toBe('id');
            expect($routes->closures['Closure(simple/{id})']->parameters->parameters[0]->optional)->toBeFalse();
        },
    ];
    yield 'nullable parameter' => [
        fn (Router $router) => $router->get('simple/{id?}', fn () => 'simple'),
        function (RouteCollection $routes) {
            expect($routes->controllers)->toBeEmpty();
            expect($routes->closures)->toHaveCount(1);

            expect($routes->closures['Closure(simple/{id?})']->url)->toBe('simple/{id}');
            expect($routes->closures['Closure(simple/{id?})']->methods)->toBe(['GET', 'HEAD']);
            expect($routes->closures['Closure(simple/{id?})']->parameters->parameters)->toHaveCount(1);
            expect($routes->closures['Closure(simple/{id?})']->parameters->parameters[0]->name)->toBe('id');
            expect($routes->closures['Closure(simple/{id?})']->parameters->parameters[0]->optional)->toBeTrue();
        },
    ];
    yield 'named routes' => [
        function (Router $router) {
            $router->get('simple', fn () => 'simple')->name('simple');
            $router->get('invokable', InvokableController::class)->name('invokable');
            $router->resource('resource', ResourceController::class);
        },
        function (RouteCollection $routes) {
            expect($routes->controllers)->toHaveCount(2);
            expect($routes->closures)->toHaveCount(1);

            expect($routes->closures['Closure(simple)']->name)->toBe('simple');

            expect($routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.InvokableController']->name)->toBe('invokable');

            $resourceController = $routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController'];

            expect($resourceController->actions['index']->name)->toBe('resource.index');
            expect($resourceController->actions['show']->name)->toBe('resource.show');
            expect($resourceController->actions['create']->name)->toBe('resource.create');
            expect($resourceController->actions['update']->name)->toBe('resource.update');
            expect($resourceController->actions['store']->name)->toBe('resource.store');
            expect($resourceController->actions['edit']->name)->toBe('resource.edit');
            expect($resourceController->actions['destroy']->name)->toBe('resource.destroy');
        },
    ];
});

it('can omit certain parts of a specified namespace', function () {
    app(Router::class)->get('error', ErrorController::class);
    app(Router::class)->get('invokable', InvokableController::class);

    $routes = app(ResolveLaravelRouteControllerCollectionsAction::class)->execute('Spatie\TypeScriptTransformer\Tests\Laravel\FakeClasses', true);

    expect($routes->controllers)->toHaveCount(2)->toHaveKeys([
        '.Symfony.Component.HttpKernel.Controller.ErrorController',
        'InvokableController',
    ]);
});

it('can filter out certain routes', function (
    RouteFilter $filter,
    Closure $expectations
) {
    $router = app(Router::class);

    $router->setRoutes(new \Illuminate\Routing\RouteCollection()); // Laravel registers a storage.local route by default, which we want to ignore in this test.

    $router->get('simple', fn () => 'simple')->name('simple');
    $router->get('invokable', InvokableController::class)->name('invokable');
    $router->resource('resource', ResourceController::class);

    $routes = app(ResolveLaravelRouteControllerCollectionsAction::class)->execute(null, true, [$filter]);

    $expectations($routes);
})->with(function () {
    yield 'named' => [
        new NamedRouteFilter('simple'),
        function (RouteCollection $routes) {
            expect($routes->closures)->toBeEmpty();
            expect($routes->controllers)->toHaveCount(2);
        },
    ];
    yield 'multiple named' => [
        new NamedRouteFilter('simple', 'resource.index', 'resource.edit'),
        function (RouteCollection $routes) {
            expect($routes->closures)->toBeEmpty();
            expect($routes->controllers)
                ->toHaveCount(2)
                ->toHaveKeys([
                    '.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController',
                    '.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.InvokableController',
                ]);
            expect($routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController']->actions)
                ->toHaveCount(5)
                ->toHaveKeys([
                    'show',
                    'create',
                    'update',
                    'store',
                    'destroy',
                ]);
        },
    ];
    yield 'wildcard name' => [
        new NamedRouteFilter('invokable', 'resource.*'),
        function (RouteCollection $routes) {
            expect($routes->closures)->toHaveCount(1);
            expect($routes->controllers)->toHaveCount(0);
        },
    ];
    yield 'controller' => [
        new ControllerRouteFilter(ResourceController::class),
        function (RouteCollection $routes) {
            expect($routes->closures)->toHaveCount(1);
            expect($routes->controllers)->toHaveCount(1)->toHaveKey('.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.InvokableController');
        },
    ];
    yield 'multiple controllers' => [
        new ControllerRouteFilter(ResourceController::class),
        function (RouteCollection $routes) {
            expect($routes->closures)->toHaveCount(1);
            expect($routes->controllers)->toHaveCount(1)->toHaveKey('.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.InvokableController');
        },
    ];
    yield 'controller wildcard' => [
        new ControllerRouteFilter('Spatie\TypeScriptTransformer\Tests\Laravel\FakeClasses\*'),
        function (RouteCollection $routes) {
            expect($routes->closures)->toHaveCount(1);
            expect($routes->controllers)->toHaveCount(0);
        },
    ];
    yield 'controller action' => [
        new ControllerRouteFilter([ResourceController::class, 'index'], [ResourceController::class, 'edit']),
        function (RouteCollection $routes) {
            expect($routes->closures)->toHaveCount(1);
            expect($routes->controllers)
                ->toHaveCount(2)
                ->toHaveKeys([
                    '.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController',
                    '.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.InvokableController',
                ]);
            expect($routes->controllers['.Spatie.TypeScriptTransformer.Tests.Laravel.FakeClasses.ResourceController']->actions)
                ->toHaveCount(5)
                ->toHaveKeys([
                    'show',
                    'create',
                    'update',
                    'store',
                    'destroy',
                ]);
        },
    ];
});
