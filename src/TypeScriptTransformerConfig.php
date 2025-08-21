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
     * @param array<class-string<TypesProvider>|TypesProvider> $typeProviders
     * @param array<string> $directoriesToWatch
     * @param array<VisitorClosure> $providedVisitorClosures
     * @param array<VisitorClosure> $connectedVisitorClosures
     * @param array<Transformer> $transformers
     * @param array<string> $configPaths
     */
    public function __construct(
        public readonly array $typeProviders,
        public readonly Writer $writer,
        public readonly ?Formatter $formatter,
        public readonly array $directoriesToWatch = [],
        public readonly array $providedVisitorClosures = [],
        public readonly array $connectedVisitorClosures = [],
        public readonly array $transformers = [],
        public readonly array $configPaths = [],
    ) {
    }
}
