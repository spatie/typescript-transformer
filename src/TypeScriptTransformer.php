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

    public function execute(bool $watch = false): TypeScriptTransformerLog
    {
        // Parallelize
        // - discovering types
        // - transforming types

        // Cant't do parallel
        // - replace type references

        // watch -> only reload when the config changes (difficult, maybe skip for now)
        $discovered = $this->discoverTypesAction->execute();

        $transformedCollection = $this->transformTypesAction->execute($discovered);

        $this->appendDefaultTypesAction->execute($transformedCollection);

        $referenceMap = $this->connectReferencesAction->execute($transformedCollection);

        $writtenFiles = $this->writeTypesAction->execute($transformedCollection, $referenceMap);

        $this->formatFilesAction->execute($writtenFiles);

        return $this->log;
    }
}
