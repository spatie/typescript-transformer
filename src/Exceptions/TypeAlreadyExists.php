<?php

namespace Spatie\TypescriptTransformer\Exceptions;

use Spatie\TypescriptTransformer\Type;
use Exception;

class TypeAlreadyExists extends Exception
{
    public static function create(Type $existingType, Type $newType)
    {
        return new self("Tried adding type {$newType->name}({$newType->class->getName()}) to file {$newType->file} which already has a type {$existingType->name}({$existingType->class->getName()})");
    }
}
