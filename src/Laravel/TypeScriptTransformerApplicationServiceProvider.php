<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Support\ServiceProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigBuilder;

abstract class TypeScriptTransformerApplicationServiceProvider extends ServiceProvider
{
    abstract protected function configure(TypeScriptTransformerConfigBuilder $config): void;

    public function register(): void
    {
        $builder = new TypeScriptTransformerConfigBuilder();

        $this->configure($builder);

        $config = $builder->get();

        $this->app->singleton(TypeScriptTransformerConfig::class, fn () => $config);
    }
}
