<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TranspilationResult;
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

        $trProps = $this->transformProperties($class, $missingSymbols);
        $trMethods = $this->transformMethods($class, $missingSymbols);
        $trExtra = $this->transformExtra($class, $missingSymbols);
        $type = join('', [
            $trProps->typescript,
            $trMethods->typescript,
            $trExtra->typescript,
        ]);

        return TransformedType::create(
            $class,
            $name,
            new TranspilationResult(
                array_merge(
                    $trProps->dependencies,
                    $trMethods->dependencies,
                    $trExtra->dependencies
                ),
                "{" . PHP_EOL . $type . "}"
            ),
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
    ): TranspilationResult {
        $isClassOptional = ! empty($class->getAttributes(Optional::class));
        $nullablesAreOptional = $this->config->shouldConsiderNullAsOptional();

        return array_reduce(
            $this->resolveProperties($class),
            function (TranspilationResult $carry, ReflectionProperty $property) use ($isClassOptional, $missingSymbols, $nullablesAreOptional) {
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

                return new TranspilationResult(
                    array_merge(
                        $carry->dependencies,
                        $transformed->dependencies
                    ),
                    $isOptional
                        ? "{$carry->typescript}{$propertyName}?: {$transformed->typescript};" . PHP_EOL
                        : "{$carry->typescript}{$propertyName}: {$transformed->typescript};" . PHP_EOL
                );
            },
            new TranspilationResult([], '')
        );
    }

    protected function transformMethods(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): TranspilationResult {
        return new TranspilationResult([], '');
    }

    protected function transformExtra(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): TranspilationResult {
        return new TranspilationResult([], '');
    }

    protected function transformPropertyName(
        ReflectionProperty $property,
        MissingSymbolsCollection $missingSymbols
    ): TranspilationResult {
        return new TranspilationResult([], $property->getName());
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
