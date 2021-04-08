<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

abstract class Collector
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    abstract public function getTransformedType(ReflectionClass $class): ?TransformedType;
}
