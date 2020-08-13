<?php

namespace Spatie\TypescriptTransformer\Transformers;

use phpDocumentor\Reflection\TypeResolver;
use ReflectionClass;
use ReflectionProperty;
use Spatie\TypescriptTransformer\Actions\ResolveClassPropertyTypeAction;
use Spatie\TypescriptTransformer\Actions\TransformClassPropertyTypeAction;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\ApplyNeverClassPropertyProcessor;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\DtoCollectionClassPropertyProcessor;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\ReplaceDefaultTypesClassPropertyProcessor;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Structures\Type;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class DtoTransformer implements Transformer
{
    private TypeScriptTransformerConfig $config;

    public function __construct(TypeScriptTransformerConfig $config)
    {
        $this->config = $config;
    }

    public function canTransform(ReflectionClass $class): bool
    {
        return true;
    }

    public function transform(ReflectionClass $class, string $name): Type
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

        return Type::create(
            $class,
            $name,
            $output,
            $missingSymbols
        );
    }

    /**
     * @return \Spatie\TypescriptTransformer\ClassPropertyProcessors\ClassPropertyProcessor[]
     * @throws \Spatie\TypescriptTransformer\Exceptions\InvalidClassPropertyReplacer
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
