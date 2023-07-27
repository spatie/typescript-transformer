<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\DefaultTypeProviders\DefaultTypesProvider;
use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\Writers\Writer;

readonly class TypeScriptTransformerConfig
{
    /**
     * @param  array<string>  $directories
     * @param  array<Transformer>  $transformers
     * @param  array<class-string<DefaultTypesProvider>>  $defaultTypeProviders
     */
    public function __construct(
        public array $directories,
        public array $transformers,
        public array $defaultTypeProviders,
        public Writer $writer,
        public ?Formatter $formatter
    ) {
    }
}
