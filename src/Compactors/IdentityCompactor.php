<?php

namespace Spatie\TypeScriptTransformer\Compactors;

class IdentityCompactor implements Compactor
{

    public function compact(string $typescriptIdentifier): string {
        return $typescriptIdentifier;
    }

}