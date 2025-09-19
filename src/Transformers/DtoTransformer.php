<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptExtraTypes;
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
            $this->transformLiteralTypeScriptExtraTypes($class, $missingSymbols),
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
        $isClassOptional = ! empty($class->getAttributes(Optional::class));
        $nullablesAreOptional = $this->config->shouldConsiderNullAsOptional();

        return array_reduce(
            $this->resolveProperties($class),
            function (string $carry, ReflectionProperty $property) use ($isClassOptional, $missingSymbols, $nullablesAreOptional) {
                $isHidden = ! empty($property->getAttributes(Hidden::class));

                if ($isHidden) {
                    return $carry;
                }

                $isOptional = $isClassOptional
                    || ! empty($property->getAttributes(Optional::class))
                    || ($property->getType()?->allowsNull() && $nullablesAreOptional);

                $transformed = $this->reflectionToTypeScript(
                    $property,
                    $missingSymbols,
                    $isOptional,
                    ...$this->typeProcessors()
                );

                if ($transformed === null) {
                    return $carry;
                }

                $propertyName = $this->transformPropertyName($property, $missingSymbols);

                return $isOptional
                    ? "{$carry}{$propertyName}?: {$transformed};" . PHP_EOL
                    : "{$carry}{$propertyName}: {$transformed};" . PHP_EOL;
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

    protected function transformLiteralTypeScriptExtraTypes(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        $attributes = $class->getAttributes(LiteralTypeScriptExtraTypes::class);

        if (empty($attributes)) {
            return '';
        }

        $attribute = $attributes[0]->newInstance();
        $extras = $attribute->getExtras();

        $result = '';
        foreach ($extras as $key => $value) {
            $result .= "{$key}: {$value};" . PHP_EOL;
        }

        return $result;
    }

    protected function transformPropertyName(
        ReflectionProperty $property,
        MissingSymbolsCollection $missingSymbols
    ): string {
        return $property->getName();
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
