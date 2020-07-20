<?php


namespace Spatie\TypescriptTransformer\Collectors;

use ReflectionClass;
use Spatie\TypescriptTransformer\Structures\TypeOccurrence;
use Spatie\TypescriptTransformer\Transformers\Transformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

interface Collector
{
    public function __construct(TypeScriptTransformerConfig $config);

    public function shouldTransform(ReflectionClass $class): bool;

    public function getTypeOccurrence(ReflectionClass $class): TypeOccurrence;
}
