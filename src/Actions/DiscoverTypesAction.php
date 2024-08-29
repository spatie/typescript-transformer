<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\Enums\DiscoveredStructureType;

class DiscoverTypesAction
{
    /**
     * @param  array<string>  $directories
     * @return array<class-string>
     */
    public function execute(
        array $directories = [],
    ): array {
        return Discover::in(...$directories)
            ->types(
                DiscoveredStructureType::ClassDefinition,
                DiscoveredStructureType::Enum,
                DiscoveredStructureType::Interface
            )
            ->get();
    }
}
