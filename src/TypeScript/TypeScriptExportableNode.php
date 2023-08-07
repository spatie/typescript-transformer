<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

interface TypeScriptExportableNode
{
    public function getExportedName(): string;
}
