<?php

namespace Spatie\TypeScriptTransformer\Exceptions;

use Exception;

class SymbolAlreadyExists extends Exception
{
    public static function whenAddingNamespace(string $namespace, array $existing): self
    {
        ['kind' => $kind, 'value' => $value] = $existing;

        return new self("Tried adding namespace: {$namespace} but a {$kind} already exists: $value");
    }

    public static function whenAddingType(string $type, array $existing): self
    {
        ['kind' => $kind, 'value' => $value] = $existing;

        return new self("Tried adding type: {$type} but a {$kind} already exists: $value");
    }
}
