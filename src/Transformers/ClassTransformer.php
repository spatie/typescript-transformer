<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpTypeNodeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptTypeAttributeContract;
use Spatie\TypeScriptTransformer\ClassPropertyProcessors\FixArrayLikeStructuresClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpPropertyNode;
use Spatie\TypeScriptTransformer\References\PhpClassReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedClass;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGenericTypeParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;

abstract class ClassTransformer implements Transformer
{
    protected array $classPropertyProcessors;

    public function __construct(
        protected DocTypeResolver $docTypeResolver = new DocTypeResolver(),
        protected TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptTypeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
        protected TranspilePhpTypeNodeToTypeScriptNodeAction $transpilePhpTypeNodeToTypeScriptTypeAction = new TranspilePhpTypeNodeToTypeScriptNodeAction(),
    ) {
        $this->classPropertyProcessors = $this->classPropertyProcessors();
    }

    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable
    {
        if ($phpClassNode->isEnum() || $phpClassNode->isInterface()) {
            return Untransformable::create();
        }

        if (! $this->shouldTransform($phpClassNode)) {
            return Untransformable::create();
        }

        $parsedClass = $this->docTypeResolver->class($phpClassNode);

        $identifier = new TypeScriptIdentifier($context->name);

        $templates = $parsedClass->templates ?? [];

        if (! empty($templates)) {
            $identifier = new TypeScriptGeneric(
                $identifier,
                array_map(
                    fn (string $name) => new TypeScriptGenericTypeParameter(new TypeScriptIdentifier($name)),
                    $templates
                )
            );
        }

        return new Transformed(
            new TypeScriptAlias(
                $identifier,
                $this->getTypeScriptNode($phpClassNode, $context, $parsedClass)
            ),
            new PhpClassReference($phpClassNode),
            $context->nameSpaceSegments,
            true,
        );
    }

    abstract protected function shouldTransform(PhpClassNode $phpClassNode): bool;

    /** @return array<ClassPropertyProcessor> */
    protected function classPropertyProcessors(): array
    {
        return [
            new FixArrayLikeStructuresClassPropertyProcessor(),
        ];
    }

    protected function getTypeScriptNode(
        PhpClassNode $phpClassNode,
        TransformationContext $context,
        ?ParsedClass $parsedClass = null,
    ): TypeScriptNode {
        if ($resolvedAttributeType = $this->resolveTypeByAttribute($phpClassNode)) {
            return $resolvedAttributeType;
        }

        $classAnnotations = $parsedClass->properties ?? [];

        $constructorAnnotations = $phpClassNode->hasMethod('__construct')
            ? $this->docTypeResolver->method($phpClassNode->getMethod('__construct'))->parameters ?? []
            : [];

        $properties = [];

        foreach ($this->getProperties($phpClassNode) as $phpPropertyNode) {
            $annotation = $classAnnotations[$phpPropertyNode->getName()]
                ?? $constructorAnnotations[$phpPropertyNode->getName()]
                ?? $this->docTypeResolver->property($phpPropertyNode)
                ?? null;

            $property = $this->createProperty(
                $phpClassNode,
                $phpPropertyNode,
                $annotation?->type,
                $context,
                $parsedClass
            );

            if ($property === null) {
                continue;
            }

            $property = $this->runClassPropertyProcessors(
                $phpPropertyNode,
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
        PhpClassNode $phpClassNode,
        ?PhpPropertyNode $property = null,
    ): ?TypeScriptNode {
        $subject = $property ?? $phpClassNode;

        foreach ($subject->getAttributes() as $attribute) {
            if (is_a($attribute->getName(), TypeScriptTypeAttributeContract::class, true)) {
                /** @var TypeScriptTypeAttributeContract $attributeInstance */
                $attributeInstance = $attribute->newInstance();

                return $attributeInstance->getType($phpClassNode);
            }
        }

        return null;
    }

    /** @return array<PhpPropertyNode> */
    protected function getProperties(PhpClassNode $phpClassNode): array
    {
        return array_filter(
            $phpClassNode->getProperties(\ReflectionProperty::IS_PUBLIC),
            fn (PhpPropertyNode $property) => ! $property->isStatic()
        );
    }

    protected function createProperty(
        PhpClassNode $phpClassNode,
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TransformationContext $context,
        ?ParsedClass $parsedClass = null,
    ): ?TypeScriptProperty {
        $type = $this->resolveTypeForProperty(
            $phpClassNode,
            $phpPropertyNode,
            $annotation,
            $parsedClass
        );

        $property = new TypeScriptProperty(
            $phpPropertyNode->getName(),
            $type,
            $this->isPropertyOptional(
                $phpPropertyNode,
                $phpClassNode,
                $type,
                $context
            ),
            $this->isPropertyReadonly(
                $phpPropertyNode,
                $phpClassNode,
                $type,
            )
        );

        if ($this->isPropertyHidden($phpPropertyNode, $phpClassNode, $property)) {
            return null;
        }

        return $property;
    }

    protected function resolveTypeForProperty(
        PhpClassNode $phpClassNode,
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        ?ParsedClass $parsedClass = null,
    ): TypeScriptNode {
        if ($resolvedAttributeType = $this->resolveTypeByAttribute($phpClassNode, $phpPropertyNode)) {
            return $resolvedAttributeType;
        }

        if ($annotation) {
            return $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation,
                $phpClassNode,
                $parsedClass->templates ?? [],
            );
        }

        if ($phpPropertyNode->hasType()) {
            return $this->transpilePhpTypeNodeToTypeScriptTypeAction->execute(
                $phpPropertyNode->getType(),
                $phpClassNode
            );
        }

        return new TypeScriptUnknown();
    }

    protected function isPropertyOptional(
        PhpPropertyNode $phpPropertyNode,
        PhpClassNode $phpClassNode,
        TypeScriptNode $type,
        TransformationContext $context,
    ): bool {
        return $context->optional || count($phpPropertyNode->getAttributes(Optional::class)) > 0;
    }

    protected function isPropertyReadonly(
        PhpPropertyNode $phpPropertyNode,
        PhpClassNode $phpClassNode,
        TypeScriptNode $type,
    ): bool {
        return $phpPropertyNode->isReadOnly() || $phpClassNode->isReadOnly();
    }

    protected function isPropertyHidden(
        PhpPropertyNode $phpPropertyNode,
        PhpClassNode $phpClassNode,
        TypeScriptProperty $property,
    ): bool {
        return count($phpPropertyNode->getAttributes(Hidden::class)) > 0;
    }

    protected function runClassPropertyProcessors(
        PhpPropertyNode $phpPropertyNode,
        ?TypeNode $annotation,
        TypeScriptProperty $property,
    ): ?TypeScriptProperty {
        $processors = $this->classPropertyProcessors;

        foreach ($processors as $processor) {
            $property = $processor->execute($phpPropertyNode, $annotation, $property);

            if ($property === null) {
                return null;
            }
        }

        return $property;
    }
}
