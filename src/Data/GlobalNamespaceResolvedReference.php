<?php

namespace Spatie\TypeScriptTransformer\Data;

class GlobalNamespaceResolvedReference
{
    public function __construct(
        public string $qualifiedName,
    ) {
    }
}
