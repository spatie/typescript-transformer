<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\ParseUseDefinitionsAction;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptTypeAction;
use Spatie\TypeScriptTransformer\Actions\TranspileReflectionTypeToTypeScriptTypeAction;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedNameAndType;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnknown;

abstract class ClassTransformer implements Transformer
{
    public function __construct(
        protected DocTypeResolver $docTypeResolver = new DocTypeResolver(),
        protected TranspilePhpStanTypeToTypeScriptTypeAction $transpilePhpStanTypeToTypeScriptTypeAction = new TranspilePhpStanTypeToTypeScriptTypeAction(),
        protected TranspileReflectionTypeToTypeScriptTypeAction $transpileReflectionTypeToTypeScriptTypeAction = new TranspileReflectionTypeToTypeScriptTypeAction(),
        protected ParseUseDefinitionsAction $parseUseDefinitionsAction = new ParseUseDefinitionsAction(),
    ) {
    }

    public function transform(ReflectionClass $reflectionClass, TransformationContext $context): Transformed|Untransformable
    {
        if (! $this->shouldTransform($reflectionClass)) {
            return Untransformable::create();
        }

        $classAnnotations = $this->docTypeResolver->class($reflectionClass)?->properties ?? [];

        $constructorAnnotations = $reflectionClass->hasMethod('__construct')
            ? $this->docTypeResolver->method($reflectionClass->getMethod('__construct'))?->parameters ?? []
            : [];

        $properties = [];

        foreach ($this->getProperties($reflectionClass) as $reflectionProperty) {
            $properties[] = $this->createProperty(
                $reflectionClass,
                $reflectionProperty,
                $classAnnotations,
                $constructorAnnotations,
            );
        }

        return new Transformed(
            new TypeScriptExport(new TypeScriptAlias(new TypeScriptIdentifier($context->name), new TypeScriptObject($properties))),
            new ReflectionClassReference($reflectionClass),
            $context->name,
            true,
            $context->nameSpaceSegments,
        );
    }

    abstract public function shouldTransform(ReflectionClass $reflection): bool;

    protected function getProperties(ReflectionClass $reflection): array
    {
        return array_values(array_filter(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $property) => ! $property->isStatic()
        ));
    }

    protected function createProperty(
        ReflectionClass $reflectionClass,
        ReflectionProperty $reflectionProperty,
        array $classAnnotations,
        array $constructorAnnotations,
    ): TypeScriptProperty {
        $propertyAnnotation = $this->docTypeResolver->property($reflectionProperty);

        $type = $this->resolveTypeForProperty(
            $reflectionClass,
            $reflectionProperty,
            $classAnnotations[$reflectionProperty->getName()]
            ?? $constructorAnnotations[$reflectionProperty->getName()]
            ?? $propertyAnnotation,
        );

        return new TypeScriptProperty(
            $reflectionProperty->getName(),
            $type,
            $this->isPropertyOptional($reflectionProperty, $reflectionClass, $type),
            $this->isPropertyReadonly($reflectionProperty, $reflectionClass, $type)
        );
    }

    protected function resolveTypeForProperty(
        ReflectionClass $reflectionClass,
        ReflectionProperty $reflectionProperty,
        ?ParsedNameAndType $annotation,
    ): TypeScriptNode {
        if ($annotation) {
            return $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->type,
                $reflectionClass,
            );
        }

        if ($reflectionProperty->hasType()) {
            return $this->transpileReflectionTypeToTypeScriptTypeAction->execute(
                $reflectionProperty->getType(),
                $reflectionClass
            );
        }

        return new TypeScriptUnknown();
    }

    protected function isPropertyOptional(
        ReflectionProperty $reflectionProperty,
        ReflectionClass $reflectionClass,
        TypeScriptNode $type,
    ): bool {
        return count($reflectionProperty->getAttributes(Optional::class)) > 0;
    }

    protected function isPropertyReadonly(
        ReflectionProperty $reflectionProperty,
        ReflectionClass $reflectionClass,
        TypeScriptNode $type,
    ): bool {
        return $reflectionProperty->isReadOnly() || $reflectionClass->isReadOnly();
    }
}
