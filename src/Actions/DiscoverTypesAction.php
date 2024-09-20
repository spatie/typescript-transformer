<?php

namespace Spatie\TypeScriptTransformer\Actions;

use ReflectionClass;
use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\Enums\DiscoveredStructureType;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class DiscoverTypesAction
{
    /**
     * @param array<string> $directories
     *
     * @return array<PhpClassNode>
     */
    public function execute(
        array $directories = [],
    ): array {
        $discovered = Discover::in(...$directories)
            ->types(
                DiscoveredStructureType::ClassDefinition,
                DiscoveredStructureType::Enum,
                DiscoveredStructureType::Interface
            )
            ->get();

        return array_values(array_filter(array_map(function (string $discovered) {
            try {
                return PhpClassNode::fromReflection(new ReflectionClass($discovered));
            } catch (\ReflectionException) {
                return null;
            }
        }, $discovered)));
    }
}
