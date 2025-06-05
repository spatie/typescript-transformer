<?php

namespace Spatie\TypeScriptTransformer\Compactors;

/**
 * Shortens namespace of a typescript identifier
 */
interface Compactor
{
    public function compact(string $typescriptIdentifier): string;
}