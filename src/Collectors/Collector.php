<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Compactors\ConfigCompactor;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

abstract class Collector
{
    protected TypeScriptTransformerConfig $config;

    protected ConfigCompactor $compactor;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
        $this->compactor = new ConfigCompactor($config);
    }

    abstract public function getTransformedType(ReflectionClass $class): ?TransformedType;
}
