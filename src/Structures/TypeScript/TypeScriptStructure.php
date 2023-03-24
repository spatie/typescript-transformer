<?php

namespace Spatie\TypeScriptTransformer\Structures\TypeScript;

use Stringable;

interface TypeScriptStructure extends Stringable
{
    public function replaceReference(
        string $replaceSymbol,
        string $replacement
    ): void;
}
