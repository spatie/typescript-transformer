<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;

interface Writer
{
    public function format(TypesCollection $collection): string;

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool;
}
