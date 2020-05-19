<?php

namespace Spatie\TypescriptTransformer\Mappers;

use ReflectionClass;

interface Mapper
{
    public function isValid(ReflectionClass $class): bool;

    public function map(ReflectionClass $class): array;
}
