<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultTypesTypeProcessor;
use Spatie\TypeScriptTransformer\TypeReflectors\TypeReflector;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DtoTransformer implements Transformer
{
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

        $properties = array_map(
            fn (ReflectionProperty $property) => $this->resolveTypeDefinition($property, $missingSymbols),
            $this->resolveProperties($class)
        );

        $properties = array_filter($properties);

        $output = "export type {$name} = {" . PHP_EOL;

        foreach ($properties as $property) {
            $output .= "    {$property}" . PHP_EOL;
        }

        $output .= '}' . PHP_EOL;

        return TransformedType::create(
            $class,
            $name,
            $output,
            $missingSymbols
        );
    }

    /**
     * @return \Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor[]
     * @throws \Spatie\TypeScriptTransformer\Exceptions\InvalidClassPropertyReplacer
     */
    protected function getClassPropertyProcessors(): array
    {
        return [
            new ReplaceDefaultTypesTypeProcessor(
                $this->config->getClassPropertyReplacements()
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

    protected function resolveTypeDefinition(
        ReflectionProperty $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?string {
        $type = TypeReflector::new($reflection)->reflect();

        foreach ($this->getClassPropertyProcessors() as $processor) {
            $type = $processor->process($type, $reflection);

            if ($type === null) {
                return null;
            }
        }

        $transformClassPropertyTypeAction = new TranspileTypeToTypeScriptAction(
            $missingSymbolsCollection,
            $reflection->getDeclaringClass()->getName()
        );

        return "{$reflection->getName()}: {$transformClassPropertyTypeAction->execute($type)};";
    }
}
