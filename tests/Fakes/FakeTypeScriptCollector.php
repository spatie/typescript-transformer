<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Structures\CollectedOccurrence;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;

class FakeTypeScriptCollector extends Collector
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
