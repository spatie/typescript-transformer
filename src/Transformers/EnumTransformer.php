<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use BackedEnum;
use Illuminate\Support\Collection;
use MyCLabs\Enum\Enum as MyclabsEnum;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use Spatie\Enum\Enum as SpatieEnum;
use Spatie\ModelStates\State;
use Spatie\TypeScriptTransformer\Structures\OldTransformedType;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\Transformed\TransformedCustom;
use Spatie\TypeScriptTransformer\Structures\Transformed\TransformedEnum;
use Spatie\TypeScriptTransformer\Structures\Transformed\TransformedUnion;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptEnum;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptUnionType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

abstract class EnumTransformer implements Transformer
{
    protected bool $asNativeEnum;

    public function __construct(
        TypeScriptTransformerConfig $config
    ) {
        $options = $config->getTransformerOptions(static::class);

        $this->asNativeEnum = $options['as_native_enum'] ?? false;
    }

    abstract protected function getOptions(ReflectionClass $class): Collection;

    public function transform(ReflectionClass $class, ?string $name = null): Transformed
    {
        $options = $this->getOptions($class);

        $reference = TypeReference::fromFqcn($class->getName(), $name);

        $structure = $this->asNativeEnum
            ? $this->resolveEnumStructure($reference->getTypeScriptName(), $options)
            : new TypeScriptAlias($reference->getTypeScriptName(), $this->resolveUnionStructure($options));

        return new Transformed(
            $reference,
            $structure,
        );
    }

    protected function resolveEnumStructure(string $name, Collection $options): TypeScriptEnum
    {
        return new TypeScriptEnum($name, $options->all());
    }

    protected function resolveUnionStructure(Collection $options): TypeScriptUnionType
    {
        return new TypeScriptUnionType(
            $options->map(fn(mixed $value) => is_string($value) ? "'{$value}'" : $value)
                ->values()
                ->all()
        );
    }
}
