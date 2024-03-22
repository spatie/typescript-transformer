<?php

namespace Spatie\TypeScriptTransformer;

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\FormatFilesAction;
use Spatie\TypeScriptTransformer\Actions\ProvideTypesAction;
use Spatie\TypeScriptTransformer\Actions\WriteFilesAction;
use Spatie\TypeScriptTransformer\Visitor\Visitor;

class TypeScriptTransformer
{
    public function __construct(
        protected TypeScriptTransformerConfig $config,
        protected DiscoverTypesAction $discoverTypesAction,
        protected ProvideTypesAction $provideTypesAction,
        protected ConnectReferencesAction $connectReferencesAction,
        protected WriteFilesAction $writeFilesAction,
        protected FormatFilesAction $formatFilesAction,
    ) {

    }

    public static function create(TypeScriptTransformerConfig $config): self
    {
        return new self(
            $config,
            new DiscoverTypesAction(),
            new ProvideTypesAction($config),
            new ConnectReferencesAction(),
            new WriteFilesAction($config),
            new FormatFilesAction($config),
        );
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
         * Watch implementation
         * - We care about file create, update and delete
         * - Directory changes are basically combined operations of file changes
         * - File create
         *  - Run the file though `TransformerTypesProvider` and check if a ReflectionClass can be created
         *  - If so, add it to the types collection
         *  - Add it to the reference map
         *  - Rewrite the file (partially)
         */

        /**
         * Notes after knowledge sharing
         * - Split Laravel part again?
         * - Make it possible to hijack the PHPStan types, or some way to rename a Laravel Collection to an array? Would be easier
         * - When generating routes where we have the full namespace, prepend with ., check Laravel Echo for this
         * - Prettier can run on complete directories, so formatting single files is maybe not required
         */
        $transformedCollection = $this->provideTypesAction->execute();

        if (! empty($this->config->providedVisitorClosures)) {
            $visitor = Visitor::create()->closures(...$this->config->providedVisitorClosures);

            foreach ($transformedCollection as $transformed) {
                $visitor->execute($transformed->typeScriptNode);
            }
        }

        $referenceMap = $this->connectReferencesAction->execute($transformedCollection);

        if (! empty($this->config->connectedVisitorClosures)) {
            $visitor = Visitor::create()->closures(...$this->config->connectedVisitorClosures);

            foreach ($transformedCollection as $transformed) {
                $visitor->execute($transformed->typeScriptNode);
            }
        }

        $writeableFiles = $this->config->writer->output($transformedCollection, $referenceMap);

        $this->writeFilesAction->execute($writeableFiles);

        $this->formatFilesAction->execute($writeableFiles);
    }
}
