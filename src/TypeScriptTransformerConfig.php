<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Formatters\Formatter;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformedProvider;
use Spatie\TypeScriptTransformer\Transformers\Transformer;
use Spatie\TypeScriptTransformer\Visitor\VisitorClosure;
use Spatie\TypeScriptTransformer\Writers\Writer;

class TypeScriptTransformerConfig
{
    /**
     * @param array<TransformedProvider> $transformedProviders
     * @param array<string> $directoriesToWatch
     * @param array<VisitorClosure> $providedVisitorClosures
     * @param array<VisitorClosure> $connectedVisitorClosures
     * @param array<Transformer> $transformers
     * @param array<string> $configPaths
     */
    public function __construct(
        public readonly string $outputDirectory,
        public readonly array $transformedProviders,
        public readonly Writer $typesWriter,
        public readonly ?Formatter $formatter,
        public readonly array $directoriesToWatch = [],
        public readonly array $providedVisitorClosures = [],
        public readonly array $connectedVisitorClosures = [],
        public readonly array $transformers = [],
        public readonly array $configPaths = [],
    ) {
    }
}
