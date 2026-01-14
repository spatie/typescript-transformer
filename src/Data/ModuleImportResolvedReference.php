<?php

namespace Spatie\TypeScriptTransformer\Data;

class ModuleImportResolvedReference
{
    public function __construct(
        public string $name,
        public string $path,
    ) {
    }
}
