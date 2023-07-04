<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

interface TypeScriptNodeWithChildren
{
    /** @return array<TypeScriptNode> */
    public function children(): array;
}
