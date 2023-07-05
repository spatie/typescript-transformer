<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Support\WrittenFile;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class WriteTypesAction
{
    public function __construct(
        public TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
    ) {
    }

    /** @return array<WrittenFile> */
    public function execute(
        TransformedCollection $collection,
        ReferenceMap $referenceMap
    ): array {
        return $this->config->writer->output($collection, $referenceMap);
    }
}
