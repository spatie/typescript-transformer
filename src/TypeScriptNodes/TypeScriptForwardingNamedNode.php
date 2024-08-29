<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

interface TypeScriptForwardingNamedNode
{
    public function getForwardedNamedNode(): TypeScriptNamedNode|TypeScriptForwardingNamedNode;
}
