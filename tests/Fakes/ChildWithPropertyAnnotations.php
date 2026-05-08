<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;

/**
 * @property string[]|SimpleClass $childItemsFromClass
 */
class ChildWithPropertyAnnotations extends ParentWithPropertyAnnotations
{
    /** @var string[]|SimpleClass */
    public array|SimpleClass $childItems;

    public $childItemsFromClass;

    /**
     * @param string[]|SimpleClass $childItemsFromConstructor
     */
    public function __construct(
        public array|SimpleClass $childItemsFromConstructor,
    ) {
        parent::__construct([]);
    }
}
