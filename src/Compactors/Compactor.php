<?php

namespace Spatie\TypeScriptTransformer\Compactors;

/**
 * Shortens namespace of a typescript identifier
 */
interface Compactor
{
    public function removeSuffix(string $typeName): string;

    public function removePrefix(string $namespace): string;
}