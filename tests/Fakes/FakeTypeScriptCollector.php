<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class FakeTypeScriptCollector extends Collector
{
    public function shouldCollect(ReflectionClass $class): bool
    {
        return is_subclass_of($class->getName(), Enum::class);
    }

    public function getTransformedType(ReflectionClass $class): TransformedType
    {
        return new TransformedType(
            $class,
            $class->getShortName(),
            'fake-collected-class',
            new MissingSymbolsCollection(),
            false
        );
    }
}
