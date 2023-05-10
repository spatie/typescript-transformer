<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
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

    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if (! $this->canTransform($class)) {
            return null;
        }

        $missingSymbols = new MissingSymbolsCollection();

        $type = join([
            $this->transformProperties($class, $missingSymbols),
            $this->transformMethods($class, $missingSymbols),
            $this->transformExtra($class, $missingSymbols),
        ]);

        return TransformedType::create(
            $class,
            $name,
            "{" . PHP_EOL . $type . "}",
            $missingSymbols
        );
    }

    protected function canTransform(ReflectionClass $class): bool
    {
        return true;
    }

    protected function transformProperties(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        $isOptional = ! empty($class->getAttributes(Optional::class));

        return array_reduce(
            $this->resolveProperties($class),
            function (string $carry, ReflectionProperty $property) use ($isOptional, $missingSymbols) {
                $isHidden = ! empty($property->getAttributes(Hidden::class));

                if ($isHidden) {
                    return $carry;
                }

                $isOptional = $isOptional || ! empty($property->getAttributes(Optional::class));

                $transformed = $this->reflectionToTypeScript(
                    $property,
                    $missingSymbols,
                    ...$this->typeProcessors()
                );

                if ($transformed === null) {
                    return $carry;
                }

                return $isOptional
                    ? "{$carry}{$property->getName()}?: {$transformed};" . PHP_EOL
                    : "{$carry}{$property->getName()}: {$transformed};" . PHP_EOL;
            },
            ''
        );
    }

    protected function transformMethods(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        return '';
    }

    protected function transformExtra(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        return '';
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
