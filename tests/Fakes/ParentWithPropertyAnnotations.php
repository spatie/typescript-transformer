<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\GenericClass;

/**
 * @property string[]|GenericClass<int> $itemsFromClass
 */
class ParentWithPropertyAnnotations
{
    /** @var string[]|GenericClass<int> */
    public array|GenericClass $items;

    public $itemsFromClass;

    /**
     * @param string[]|GenericClass<int> $itemsFromConstructor
     */
    public function __construct(
        public array|GenericClass $itemsFromConstructor,
    ) {
    }
}
