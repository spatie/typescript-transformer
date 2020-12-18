<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Transformers\Transformer;

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
