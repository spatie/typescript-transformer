<?php

namespace Spatie\TypeScriptTransformer\Transformers\EnumProviders;

use ReflectionClass;

interface EnumProvider
{
    public function isEnum(ReflectionClass $reflection): bool;

    public function isValidUnion(ReflectionClass $reflection): bool;

    public function resolveCases(ReflectionClass $reflection): array;
}
