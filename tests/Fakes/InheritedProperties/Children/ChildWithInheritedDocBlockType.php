<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\Children;

use Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\ParentWithDocBlockType;

/**
 * This child class is in a different namespace than the parent.
 * It does NOT import SomeGenericClass — it inherits $items from
 * the parent, whose @var docblock references SomeGenericClass.
 */
class ChildWithInheritedDocBlockType extends ParentWithDocBlockType
{
    public string $ownProperty;
}
