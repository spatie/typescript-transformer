<?php

namespace Spatie\TypeScriptTransformer\Tests\Laravel;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TypeScriptTransformer\Laravel\TypeScriptTransformerServiceProvider;

abstract class LaravelTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            TypeScriptTransformerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

