<?php

namespace Spatie\TypeScriptTransformer\Writers;

use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

interface Writer
{
    public function __construct(TypeScriptTransformerConfig $config);

    public function format(TypesCollection $collection): void;

    public function replacesSymbolsWithFullyQualifiedIdentifiers(): bool;
}
