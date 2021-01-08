<?php

namespace Spatie\TypeScriptTransformer\OutputFormatters;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;

interface OutputFormatter
{
    public function format(TypesCollection $collection): string;

    public function replaceMissingSymbols(TypesCollection $collection): self;
}
