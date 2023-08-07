<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

interface TypeScriptForwardingExportableNode
{
    public function getForwardedExportableNode(): TypeScriptExportableNode|TypeScriptForwardingExportableNode;
}
