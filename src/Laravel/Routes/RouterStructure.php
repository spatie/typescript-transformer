<?php

namespace Spatie\TypeScriptTransformer\Laravel\Routes;

use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

interface RouterStructure
{
    public function toTypeScriptNode(): TypeScriptNode;

    public function toJsObject(): array;
}
