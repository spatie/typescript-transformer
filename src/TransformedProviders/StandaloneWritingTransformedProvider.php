<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Writers\Writer;

interface StandaloneWritingTransformedProvider
{
    public function getWriter(): Writer;
}
