<?php


namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypescriptTransformer\ValueObjects\ClassOccurrence;

abstract class Collector
{
    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    abstract public function shouldTransform(ReflectionClass $class): bool;

    abstract public function getClassOccurrence(ReflectionClass $class): ClassOccurrence;
}
