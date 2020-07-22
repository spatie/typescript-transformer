<?php


namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypescriptTransformer\ValueObjects\ClassOccurrence;

interface Collector
{
    public function __construct(TypeScriptTransformerConfig $config);

    public function shouldTransform(ReflectionClass $class): bool;

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence;
}
