<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspileReflectionTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedMethod;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedNameAndType;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptInterface;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptInterfaceMethod;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptParameter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

abstract class InterfaceTransformer implements Transformer
{
    public function __construct(
        protected DocTypeResolver $docTypeResolver = new DocTypeResolver(),
        protected TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptTypeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
        protected TranspileReflectionTypeToTypeScriptNodeAction $transpileReflectionTypeToTypeScriptTypeAction = new TranspileReflectionTypeToTypeScriptNodeAction(),
    ) {
    }

    public function transform(ReflectionClass $reflectionClass, TransformationContext $context): Transformed|Untransformable
    {
        if (! $reflectionClass->isInterface()) {
            return Untransformable::create();
        }

        if (! $this->shouldTransform($reflectionClass)) {
            return Untransformable::create();
        }

        $node = new TypeScriptInterface(
            new TypeScriptIdentifier($context->name),
            $this->getProperties($reflectionClass, $context),
            $this->getMethods($reflectionClass, $context)
        );

        return new Transformed(
            $node,
            new ReflectionClassReference($reflectionClass),
            $context->nameSpaceSegments,
            true,
        );
    }

    abstract protected function shouldTransform(ReflectionClass $reflection): bool;

    /** @return TypeScriptInterfaceMethod[] */
    protected function getMethods(
        ReflectionClass $reflectionClass,
        TransformationContext $context,
    ): array {
        $methods = [];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $methods[] = $this->getTypeScriptMethod($reflectionClass, $reflectionMethod, $context);
        }

        return $methods;
    }

    /** @return TypeScriptProperty[] */
    protected function getProperties(
        ReflectionClass $reflectionClass,
        TransformationContext $context,
    ): array {
        return [];
    }

    protected function getTypeScriptMethod(
        ReflectionClass $reflectionClass,
        ReflectionMethod $reflectionMethod,
        TransformationContext $context,
    ): TypeScriptInterfaceMethod {
        $annotation = $this->docTypeResolver->method($reflectionMethod);

        return new TypeScriptInterfaceMethod(
            $reflectionMethod->getName(),
            array_map(fn (ReflectionParameter $parameter) => $this->resolveMethodParameterType(
                $reflectionClass,
                $reflectionMethod,
                $parameter,
                $context,
                $annotation->parameters[$parameter->getName()] ?? null
            ), $reflectionMethod->getParameters()),
            $this->resolveMethodReturnType($reflectionClass, $reflectionMethod, $context, $annotation)
        );
    }

    protected function resolveMethodReturnType(
        ReflectionClass $reflectionClass,
        ReflectionMethod $reflectionMethod,
        TransformationContext $context,
        ?ParsedMethod $annotation
    ): TypeScriptNode {
        if ($annotation->returnType) {
            return $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->returnType,
                $reflectionClass
            );
        }

        $reflectionType = $reflectionMethod->getReturnType();

        if ($reflectionType) {
            return $this->transpileReflectionTypeToTypeScriptTypeAction->execute(
                $reflectionType,
                $reflectionClass
            );
        }

        return new TypeScriptVoid();
    }

    protected function resolveMethodParameterType(
        ReflectionClass $reflectionClass,
        ReflectionMethod $reflectionMethod,
        ReflectionParameter $reflectionParameter,
        TransformationContext $context,
        ?ParsedNameAndType $annotation,
    ): TypeScriptParameter {
        $type = match (true) {
            $annotation !== null => $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->type,
                $reflectionClass
            ),
            $reflectionParameter->hasType() => $this->transpileReflectionTypeToTypeScriptTypeAction->execute(
                $reflectionParameter->getType(),
                $reflectionClass
            ),
            default => new TypeScriptUnknown(),
        };

        return new TypeScriptParameter(
            $reflectionParameter->getName(),
            $type,
            $reflectionParameter->isOptional()
        );
    }
}
