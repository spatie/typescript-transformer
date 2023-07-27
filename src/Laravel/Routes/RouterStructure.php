<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

interface RouterStructure
{
    public function toJsObject(): array;
}
