<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties;

class ParentWithDocBlockType
{
    /** @var string[]|SomeGenericClass<int, string> */
    public array $items;
}
