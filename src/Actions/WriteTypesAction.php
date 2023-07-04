<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\Collections\ReferenceMap;
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
        array $transformed,
        ReferenceMap $referenceMap
    ): array {
        return $this->config->writer->output($transformed, $referenceMap);
    }
}
