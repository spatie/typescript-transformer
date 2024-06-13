<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

interface TypeScriptForwardingNamedNode
{
    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode;
}
