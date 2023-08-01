<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\Enums\DiscoveredStructureType;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DiscoverTypesAction
{
    /**
     * @param array<string> $directories
     * @return array<class-string>
     */
    public function execute(
        array $directories = [],
    ): array {
        // Idea / TODO : make it possible for other packages to hook in to find types in other directories, like their vendor dir

        return Discover::in(...$directories)
            ->types(
                DiscoveredStructureType::ClassDefinition,
                DiscoveredStructureType::Enum,
                DiscoveredStructureType::Interface
            )
            ->get();
    }
}
