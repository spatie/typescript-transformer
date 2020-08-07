<?php

namespace Spatie\TypescriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypescriptTransformer\Collectors\Collector;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\Support\CollectedOccurrence;

class FakeTypescriptCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), Enum::class);
    }

    public function getCollectedOccurrence(ReflectionClass $class): CollectedOccurrence
    {
        return CollectedOccurrence::create(
            new MyclabsEnumTransformer(),
            $class->getShortName()
        );
    }
}
