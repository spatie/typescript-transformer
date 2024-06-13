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

    public static function create(TypeScriptTransformerConfig|TypeScriptTransformerConfigFactory $config): self
    {
        $config = $config instanceof TypeScriptTransformerConfigFactory ? $config->get() : $config;

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
        /**
         * TODO:
         * - Add interface implementation + tests
         * - Split off Laravel specific code and test
         * - Split off data specific code and test
         * - Add support for watching files
         * - Further write docs + check them
         * - Check old Laravel tests if we missed something
         * - Check in Flare whether everything is working as expected
         * - Release
         */

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
