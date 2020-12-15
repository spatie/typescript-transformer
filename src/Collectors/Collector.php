<?php

namespace Spatie\TypeScriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

abstract class Collector
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    abstract public function shouldCollect(ReflectionClass $class): bool;

    abstract public function getCollectedOccurrence(ReflectionClass $class): CollectedOccurrence;
}
