<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspileReflectionTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTypeAttributeContract;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnknown;

abstract class ClassTransformer implements Transformer
{
    protected array $classPropertyProcessors;

    public function __construct(
        protected DocTypeResolver $docTypeResolver = new DocTypeResolver(),
        protected TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptTypeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
        protected TranspileReflectionTypeToTypeScriptNodeAction $transpileReflectionTypeToTypeScriptTypeAction = new TranspileReflectionTypeToTypeScriptNodeAction(),
    ) {
        $this->classPropertyProcessors = $this->classPropertyProcessors();
    }

    public function transform(ReflectionClass $reflectionClass, TransformationContext $context): Transformed|Untransformable
    {
        if ($reflectionClass->isEnum()) {
            return Untransformable::create();
        }

        if (! $this->shouldTransform($reflectionClass)) {
            return Untransformable::create();
        }

        return new Transformed(
            new TypeScriptAlias(
                new TypeScriptIdentifier($context->name),
                $this->getTypeScriptNode($reflectionClass, $context)
            ),
            new ReflectionClassReference($reflectionClass),
            $context->nameSpaceSegments,
            true,
        );
    }

    abstract protected function shouldTransform(ReflectionClass $reflection): bool;

    /** @return array<ClassPropertyProcessor> */
    protected function classPropertyProcessors(): array
    {
        return [];
    }

    protected function getTypeScriptNode(
        ReflectionClass $reflectionClass,
        TransformationContext $context,
    ): TypeScriptNode {
        if ($resolvedAttributeType = $this->resolveTypeByAttribute($reflectionClass)) {
            return $resolvedAttributeType;
        }

        $classAnnotations = $this->docTypeResolver->class($reflectionClass)?->properties ?? [];

        $constructorAnnotations = $reflectionClass->hasMethod('__construct')
            ? $this->docTypeResolver->method($reflectionClass->getMethod('__construct'))?->parameters ?? []
            : [];

        $properties = [];

        foreach ($this->getProperties($reflectionClass) as $reflectionProperty) {
            $annotation = $classAnnotations[$reflectionProperty->getName()]
                ?? $constructorAnnotations[$reflectionProperty->getName()]
                ?? $this->docTypeResolver->property($reflectionProperty)
                ?? null;

            $property = $this->createProperty(
                $reflectionClass,
                $reflectionProperty,
                $annotation?->type,
                $context
            );

            if ($property === null) {
                continue;
            }

            $property = $this->runClassPropertyProcessors(
                $reflectionProperty,
                $annotation?->type,
                $property
            );

            if ($property !== null) {
                $properties[] = $property;
            }
        }

        return new TypeScriptObject($properties);
    }

    protected function resolveTypeByAttribute(
        ReflectionClass $reflectionClass,
        ?ReflectionProperty $property = null,
    ): ?TypeScriptNode {
        $subject = $property ?? $reflectionClass;

        foreach ($subject->getAttributes() as $attribute) {
            if (is_a($attribute->getName(), TypeScriptTypeAttributeContract::class, true)) {
                /** @var TypeScriptTypeAttributeContract $attributeInstance */
                $attributeInstance = $attribute->newInstance();

                return $attributeInstance->getType($reflectionClass);
            }
        }

        return null;
    }

    protected function getProperties(ReflectionClass $reflection): array
    {
        return array_filter(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $property) => ! $property->isStatic()
        );
    }

    protected function createProperty(
        ReflectionClass $reflectionClass,
        ReflectionProperty $reflectionProperty,
        ?TypeNode $annotation,
        TransformationContext $context,
    ): ?TypeScriptProperty {
        $type = $this->resolveTypeForProperty(
            $reflectionClass,
            $reflectionProperty,
            $annotation
        );

        $property = new TypeScriptProperty(
            $reflectionProperty->getName(),
            $type,
            $this->isPropertyOptional(
                $reflectionProperty,
                $reflectionClass,
                $type,
                $context
            ),
            $this->isPropertyReadonly(
                $reflectionProperty,
                $reflectionClass,
                $type,
            )
        );

        if ($this->isPropertyHidden($reflectionProperty, $reflectionClass, $property)) {
            return null;
        }

        return $property;
    }

    protected function resolveTypeForProperty(
        ReflectionClass $reflectionClass,
        ReflectionProperty $reflectionProperty,
        ?TypeNode $annotation,
    ): TypeScriptNode {
        if ($resolvedAttributeType = $this->resolveTypeByAttribute($reflectionClass, $reflectionProperty)) {
            return $resolvedAttributeType;
        }

        if ($annotation) {
            return $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation,
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
        TransformationContext $context,
    ): bool {
        return $context->optional || count($reflectionProperty->getAttributes(Optional::class)) > 0;
    }

    protected function isPropertyReadonly(
        ReflectionProperty $reflectionProperty,
        ReflectionClass $reflectionClass,
        TypeScriptNode $type,
    ): bool {
        return $reflectionProperty->isReadOnly() || $reflectionClass->isReadOnly();
    }

    protected function isPropertyHidden(
        ReflectionProperty $reflectionProperty,
        ReflectionClass $reflectionClass,
        TypeScriptProperty $property,
    ): bool {
        return count($reflectionProperty->getAttributes(Hidden::class)) > 0;
    }

    protected function runClassPropertyProcessors(
        ReflectionProperty $reflectionProperty,
        ?TypeNode $annotation,
        TypeScriptProperty $property,
    ): ?TypeScriptProperty {
        $processors = $this->classPropertyProcessors;

        foreach ($processors as $processor) {
            $property = $processor->execute($reflectionProperty, $annotation, $property);

            if ($property === null) {
                return null;
            }
        }

        return $property;
    }
}
