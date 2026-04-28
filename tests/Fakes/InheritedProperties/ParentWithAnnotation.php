<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties;

class ParentWithAnnotation
{
    /** @var string[]|SimpleGenericClass<int, string> */
    public array|SimpleGenericClass $items;
}
