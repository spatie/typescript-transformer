<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfig
{
    public array $directoriesToWatch = [];

    /**
     * @param  array<class-string<TypesProvider>|TypesProvider>  $typeProviders
     */
    public function __construct(
        readonly public array $typeProviders,
        readonly public Writer $writer,
        readonly public ?Formatter $formatter
    ) {
    }
}
