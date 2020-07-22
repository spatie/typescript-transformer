<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Collectors\Collector;
use Spatie\TypescriptTransformer\ValueObjects\ClassOccurrence;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class FakeTypescriptCollector implements Collector
{
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function shouldTransform(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), Enum::class);
    }

    public function getClassOccurrence(ReflectionClass $class): ClassOccurrence
    {
        return ClassOccurrence::create(
            new MyclabsEnumTransformer(),
            $class->getShortName()
        );
    }
}
