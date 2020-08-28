<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use phpDocumentor\Reflection\TypeResolver;
use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\ResolveClassPropertyTypeAction;
use Spatie\TypeScriptTransformer\Actions\TransformClassPropertyTypeAction;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\ApplyNeverClassPropertyProcessor;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\DtoCollectionClassPropertyProcessor;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\ReplaceDefaultTypesClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
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
     * @return \Spatie\TypeScriptTransformer\ClassPropertyProcessors\ClassPropertyProcessor[]
     * @throws \Spatie\TypeScriptTransformer\Exceptions\InvalidClassPropertyReplacer
     */
    protected function getClassPropertyProcessors(): array
    {
        return [
            new ReplaceDefaultTypesClassPropertyProcessor(
                $this->config->getClassPropertyReplacements()
            ),
            new DtoCollectionClassPropertyProcessor(),
            new ApplyNeverClassPropertyProcessor(),
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
        $resolveClassPropertyTypeAction = new ResolveClassPropertyTypeAction(
            new TypeResolver()
        );

        $type = $resolveClassPropertyTypeAction->execute($reflection);

        foreach ($this->getClassPropertyProcessors() as $processor) {
            $type = $processor->process($type, $reflection);

            if ($type === null) {
                return null;
            }
        }

        $transformClassPropertyTypeAction = new TransformClassPropertyTypeAction(
            $missingSymbolsCollection,
            $reflection->getDeclaringClass()->getName()
        );

        return "{$reflection->getName()}: {$transformClassPropertyTypeAction->execute($type)};";
    }
}
