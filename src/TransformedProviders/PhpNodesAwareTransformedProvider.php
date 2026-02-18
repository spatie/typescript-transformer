<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;

interface PhpNodesAwareTransformedProvider
{
    public function setPhpNodeCollection(PhpNodeCollection $phpNodeCollection): void;
}
