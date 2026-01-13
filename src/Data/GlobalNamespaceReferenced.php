<?php

namespace Spatie\TypeScriptTransformer\Data;

class GlobalNamespaceReferenced
{
    public function __construct(
        public string $qualifiedName,
    ) {
    }
}
