<?php

namespace Spatie\TypeScriptTransformer\Structures;

use ReflectionClass;

class NamespacedType
{

    public readonly string $namespace;

    public readonly string $shortName;

    public function __construct(
        string $type
    ) {
        $this->namespace = self::namespace($type);
        $this->shortName = self::shortName($type);
    }


    public static function namespace(string|ReflectionClass $type): string {
        if ($type instanceof ReflectionClass) {
            $type = $type->getName();
        }
        return substr($type, 0, strrpos($type, '\\'));
    }

    public static function commonPrefix(string $a, string $b): string {
        $length = min(strlen($a), strlen($b));
        $i = 0;

        while ($i < $length && $a[$i] === $b[$i]) {
            $i++;
        }

        $prefix = substr($a, 0, $i);
        $lastSlashPos = strrpos($prefix, '\\');

        return $lastSlashPos !== false ? substr($prefix, 0, $lastSlashPos + 1) : '';
    }

    public static function shortName(string|ReflectionClass $type): string {
        if ($type instanceof ReflectionClass) {
            $type = $type->getName();
        }

        $pos = strrpos($type, '\\');

        return $pos !== false ? substr($type, $pos + 1) : $type;
    }

}
