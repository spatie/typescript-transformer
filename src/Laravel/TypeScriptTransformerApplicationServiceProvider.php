<?php

namespace Spatie\TypeScriptTransformer\Laravel;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

abstract class TypeScriptTransformerApplicationServiceProvider extends ServiceProvider
{
    abstract protected function configure(TypeScriptTransformerConfigFactory $config): void;

    public function register(): void
    {
        $this->app->singleton(TypeScriptTransformerConfig::class, function () {
            $builder = new TypeScriptTransformerConfigFactory();

            $builder->configPath((new ReflectionClass($this))->getFileName());

            $this->configure($builder);

            return $builder->get();
        });
    }
}
