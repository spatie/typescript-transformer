<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

interface TypeScriptDeduplicableNode
{
    public function deduplicateNodes(): void;
}
