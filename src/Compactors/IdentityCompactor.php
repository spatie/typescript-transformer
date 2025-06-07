<?php

namespace Spatie\TypeScriptTransformer\Compactors;

class IdentityCompactor implements Compactor
{

    public function removeSuffix(string $typeName): string {
        return $typeName;
    }

    public function removePrefix(string $namespace): string {
        return $namespace;
    }
}