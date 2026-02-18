<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

interface ActionAwareTransformedProvider
{
    public function setActions(TransformedProviderActions $actions): void;
}
