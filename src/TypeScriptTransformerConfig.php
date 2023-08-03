<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfig
{
    /**
     * @param array<class-string<TypesProvider> $typeProviders
     * @param array<string> $directoriesToWatch
     */
    public function __construct(
        readonly public array $typeProviders,
        readonly public Writer $writer,
        readonly public ?Formatter $formatter,
        readonly public array $directoriesToWatch = [],
    ) {
    }
}
