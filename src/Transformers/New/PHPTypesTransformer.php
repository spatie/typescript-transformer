<?php

namespace Spatie\TypeScriptTransformer\Transformers\New;

use Exception;
use ReflectionNamedType;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptSingleType;

class PHPTypesTransformer extends NewTransformer
{
    protected function tryTransformation(
        mixed $reflection,
        array $annotations,
        ?string $alias = null,
        bool $inline = false
    ): ?Transformed {
        if (! $reflection instanceof ReflectionNamedType) {
            return null;
        }

        if ($reflection->isBuiltin()) {
            return null;
        }

        if ($reflection->getName() === 'array') {
            return $this->transformArray($annotations);
        }

        $typeScript = match ($reflection->getName()) {
            'integer', 'float' => 'number',
            'bool' => 'boolean',
            'string' => 'string',
            default => throw new Exception("Unknown default PHP type"),
        };

        return new Transformed(
            name: new TypeReference($reflection->getName(), []),
            structure: new TypeScriptSingleType($typeScript),
            inline: true,
        );
    }

    protected function transformArray(array $annotations): Transformed
    {
        return new Transformed(
            name: new TypeReference('array', []),
            structure: new TypeScriptArray(null),
            inline: true,
        );
    }
}
