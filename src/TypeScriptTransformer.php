<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Actions\ProvideTypesAction;
use Spatie\TypeScriptTransformer\Actions\WriteTypesAction;
use Spatie\TypeScriptTransformer\Support\TransformedCollection;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;

class TypeScriptTransformer
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        protected DiscoverTypesAction $discoverTypesAction,
        protected ProvideTypesAction $appendDefaultTypesAction,
        protected ConnectReferencesAction $connectReferencesAction,
        protected WriteTypesAction $writeTypesAction,
        protected FormatFilesAction $formatFilesAction,
    ) {

    }

    public function execute(bool $watch = false): void
    {
        // Parallelize
        // - discovering types
        // - transforming types

        // Cant't do parallel
        // - replace type references

        // watch -> only reload when the config changes (difficult, maybe skip for now)

        /**
         * Notes after knowledge sharing
         * - Split Laravel part again?
         * - Make it possible to hijack the PHPStan types, or some way to rename a Laravel Collection to an array? Would be easier
         * - When generating routes where we have the full namespace, prepend with ., check Laravel Echo for this
         * - Prettier can run on complete directories, so formatting single files is maybe not required
         */
        $transformedCollection = new TransformedCollection();

        $this->appendDefaultTypesAction->execute($transformedCollection);

        $referenceMap = $this->connectReferencesAction->execute($transformedCollection);

        $writtenFiles = $this->writeTypesAction->execute($transformedCollection, $referenceMap);

        $this->formatFilesAction->execute($writtenFiles);
    }
}
