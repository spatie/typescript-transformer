<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\TypeProviders\TypesProvider;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosure;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfig
{
    /**
     * @param array<class-string<TypesProvider> $typeProviders
     * @param array<string> $directoriesToWatch
     * @param array<VisitorClosure> $providedVisitorClosures
     * @param array<VisitorClosure> $connectedVisitorClosures
     * @param array<Transformer> $transformers
     */
    public function __construct(
        readonly public array $typeProviders,
        readonly public Writer $writer,
        readonly public ?Formatter $formatter,
        readonly public array $directoriesToWatch = [],
        readonly public array $providedVisitorClosures = [],
        readonly public array $connectedVisitorClosures = [],
        readonly public array $transformers = [],
    ) {
    }
}
