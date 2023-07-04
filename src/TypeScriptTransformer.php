<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\AppendDefaultTypesAction;
use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Actions\WriteTypesAction;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;

class TypeScriptTransformer
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        protected TypeScriptTransformerLog $log,
        protected DiscoverTypesAction $discoverTypesAction,
        protected TransformTypesAction $transformTypesAction,
        protected AppendDefaultTypesAction $appendDefaultTypesAction,
        protected ConnectReferencesAction $connectReferencesAction,
        protected WriteTypesAction $writeTypesAction,
        protected FormatFilesAction $formatFilesAction,
    ) {

    }

    public function execute(): TypeScriptTransformerLog
    {
        // Parallelize
        // - discovering types
        // - transforming types

        // Cant't do parallel
        // - replace type references

        $discovered = $this->discoverTypesAction->execute();

        $transformed = $this->transformTypesAction->execute($discovered);

        $transformed = $this->appendDefaultTypesAction->execute($transformed);

        $referenceMap = $this->connectReferencesAction->execute($transformed);

        $writtenFiles = $this->writeTypesAction->execute($transformed, $referenceMap);

        $this->formatFilesAction->execute($writtenFiles);

        return $this->log;
    }
}
