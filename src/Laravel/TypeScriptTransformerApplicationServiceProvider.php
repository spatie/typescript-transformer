<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Support\ServiceProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

abstract class TypeScriptTransformerApplicationServiceProvider extends ServiceProvider
{
    abstract protected function configure(TypeScriptTransformerConfigFactory $config): void;

    public function register(): void
    {
        $builder = new TypeScriptTransformerConfigFactory();

        $this->configure($builder);

        $config = $builder->get();

        $this->app->singleton(TypeScriptTransformerConfig::class, fn () => $config);
    }
}
