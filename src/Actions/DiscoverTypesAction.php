<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\Enums\DiscoveredStructureType;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DiscoverTypesAction
{
    public function __construct(
        public TypeScriptTransformerConfig $config,
        public TypeScriptTransformerLog $log,
    ) {
    }

    /** @return array<string> */
    public function execute(): array
    {
        // Idea / TODO : make it possible for other packages to hook in to find types in other directories, like their vendor dir

        return Discover::in(...$this->config->directories)
            ->types(
                DiscoveredStructureType::ClassDefinition,
                DiscoveredStructureType::Enum,
                DiscoveredStructureType::Interface
            )
            ->get();
    }
}
