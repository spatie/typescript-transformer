<?php

namespace Spatie\TypeScriptTransformer;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class TypeScriptNodeRegistry
{
    /** @var array<class-string, string[]> */
    private static array $cache = [];

    /** @return string[] */
    public static function resolve(TypeScriptNode $node): array
    {
        $class = $node::class;

        if (array_key_exists($class, self::$cache)) {
            return self::$cache[$class];
        }

        $reflection = new ReflectionClass($class);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->getAttributes(NodeVisitable::class)) {
                $properties[] = $property->getName();
            }
        }

        return self::$cache[$class] = $properties;
    }
}
