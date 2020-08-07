<?php


namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypescriptTransformer\Support\CollectedOccurrence;

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
