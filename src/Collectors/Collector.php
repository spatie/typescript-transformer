<?php


namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\ValueObjects\ClassOccurrence;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

interface Collector
{
    public function __construct(TypeScriptTransformerConfig $config);

    public function shouldTransform(ReflectionClass $class): bool;

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence;
}
