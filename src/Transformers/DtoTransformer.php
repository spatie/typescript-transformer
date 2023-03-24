<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\Structures\TypeScript\TypeScriptSingleType;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DtoTransformer implements Transformer
{
    use TransformsTypes;

    public function __construct(
        protected TypeScriptTransformerConfig $config,
    ) {
    }

    public function canTransform(ReflectionClass $class): bool
    {
        return true;
    }

    public function transform(ReflectionClass $class, ?string $name = null): Transformed
    {
        $typeReferences = new TypeReferencesCollection();

        $properties = [
            ...$this->transformProperties($class, $typeReferences),
            ...$this->transformMethods($class, $typeReferences),
            ...$this->transformExtra($class, $typeReferences),
        ];

        $named = TypeReference::fromFqcn($class->name, $name);

        $structure = new TypeScriptAlias(
            $named->getTypeScriptName(),
            new TypeScriptObject($properties),
        );

        return new Transformed(
            name: $named,
            structure: $structure,
            typeReferences: $typeReferences
        );
    }

    protected function transformProperties(
        ReflectionClass $class,
        TypeReferencesCollection $typeReferences
    ): array {
        $isOptional = ! empty($class->getAttributes(Optional::class));

        return collect($this->resolveProperties($class))
            ->map(function (ReflectionProperty $property) use ($isOptional, $typeReferences) {
                $transformed = $this->reflectionToTypeScript(
                    $property,
                    $typeReferences,
                    ...$this->typeProcessors()
                );

                if ($transformed === null) {
                    return null;
                }

                $isOptional = $isOptional || ! empty($property->getAttributes(Optional::class));

                return new TypeScriptProperty(
                    $property->getName(),
                    new TypeScriptSingleType($transformed),
                    $isOptional
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function transformMethods(
        ReflectionClass $class,
        TypeReferencesCollection $typeReferences
    ): array {
        return [];
    }

    protected function transformExtra(
        ReflectionClass $class,
        TypeReferencesCollection $typeReferences
    ): array {
        return [];
    }

    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
        ];
    }

    protected function resolveProperties(ReflectionClass $class): array
    {
        $properties = array_filter(
            $class->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $property) => ! $property->isStatic()
        );

        return array_values($properties);
    }
}
