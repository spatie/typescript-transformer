<?php

namespace Spatie\TypescriptTransformer\Support;

use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class TransformerFactory
{
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function create(string $class): Transformer
    {
        return method_exists($class, '__construct')
            ? new $class($this->config)
            : new $class;
    }
}
