<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Collectors\Collector;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypescriptTransformer\ValueObjects\ClassOccurrence;

class FakeTypescriptCollector extends Collector
{
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
