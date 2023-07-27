<?php

namespace Spatie\TypeScriptTransformer\Tests\Laravel;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TypeScriptTransformer\Laravel\TypeScriptTransformerServiceProvider;

class LaravelTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            TypeScriptTransformerServiceProvider::class,
        ];
    }
}
