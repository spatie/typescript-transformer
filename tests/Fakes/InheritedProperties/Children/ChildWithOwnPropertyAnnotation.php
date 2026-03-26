<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\Children;

use Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\ParentWithAnnotation;

/**
 * This child overrides the parent's @var annotation with its own @property
 * annotation referencing a class from the child's namespace.
 *
 * @property ChildSpecificClass $items
 */
class ChildWithOwnPropertyAnnotation extends ParentWithAnnotation
{
}
