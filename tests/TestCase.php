<?php

namespace Spatie\TypescriptTransformer\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TypescriptTransformer\TypescriptTransformerServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            TypescriptTransformerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {

    }
}
