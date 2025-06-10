<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TranspilationResult;

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
            TranspilationResult::noDeps(
                'fake-collected-class'
            ),
            new MissingSymbolsCollection(),
            false
        );
    }
}
