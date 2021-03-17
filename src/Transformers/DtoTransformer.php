<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TransformsTypes;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DtoTransformer implements Transformer
{
    use TransformsTypes;

    protected TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function canTransform(ReflectionClass $class): bool
    {
        return true;
    }

    public function transform(ReflectionClass $class, string $name): TransformedType
    {
        $missingSymbols = new MissingSymbolsCollection();

        $output = array_reduce(
            $this->resolveProperties($class),
            function (string $carry, ReflectionProperty $property) use ($missingSymbols) {
                $transformed = $this->reflectionToTypeScript(
                    $property,
                    $missingSymbols,
                    ...$this->typeProcessors()
                );

                if ($transformed === null) {
                    return $carry;
                }

                return "{$carry}{$property->getName()}: {$transformed};";
            },
            ''
        );

        return TransformedType::create(
            $class,
            $name,
            "export type {$name} = {{$output}};",
            $missingSymbols
        );
    }

    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
            new DtoCollectionTypeProcessor(),
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
