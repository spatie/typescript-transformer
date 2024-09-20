<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpTypeNodeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpMethodNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpParameterNode;
use Spatie\TypeScriptTransformer\References\PhpClassReference;
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
        protected TranspilePhpTypeNodeToTypeScriptNodeAction $transpilePhpTypeNodeToTypeScriptNodeAction = new TranspilePhpTypeNodeToTypeScriptNodeAction(),
    ) {
    }

    public function transform(PhpClassNode $phpClassNode, TransformationContext $context): Transformed|Untransformable
    {
        if (! $phpClassNode->isInterface()) {
            return Untransformable::create();
        }

        if (! $this->shouldTransform($phpClassNode)) {
            return Untransformable::create();
        }

        $node = new TypeScriptInterface(
            new TypeScriptIdentifier($context->name),
            $this->getProperties($phpClassNode, $context),
            $this->getMethods($phpClassNode, $context)
        );

        return new Transformed(
            $node,
            new PhpClassReference($phpClassNode),
            $context->nameSpaceSegments,
            true,
        );
    }

    abstract protected function shouldTransform(PhpClassNode $phpClassNode): bool;

    /** @return TypeScriptInterfaceMethod[] */
    protected function getMethods(
        PhpClassNode $phpClassNode,
        TransformationContext $context,
    ): array {
        $methods = [];

        foreach ($phpClassNode->getMethods() as $phpMethodNode) {
            $methods[] = $this->getTypeScriptMethod($phpClassNode, $phpMethodNode, $context);
        }

        return $methods;
    }

    /** @return TypeScriptProperty[] */
    protected function getProperties(
        PhpClassNode $phpClassNode,
        TransformationContext $context,
    ): array {
        return [];
    }

    protected function getTypeScriptMethod(
        PhpClassNode $phpClassNode,
        PhpMethodNode $phpMethodNode,
        TransformationContext $context,
    ): TypeScriptInterfaceMethod {
        $annotation = $this->docTypeResolver->method($phpMethodNode);

        return new TypeScriptInterfaceMethod(
            $phpMethodNode->getName(),
            array_map(fn (PhpParameterNode $parameterNode) => $this->resolveMethodParameterType(
                $phpClassNode,
                $phpMethodNode,
                $parameterNode,
                $context,
                $annotation->parameters[$parameterNode->getName()] ?? null
            ), $phpMethodNode->getParameters()),
            $this->resolveMethodReturnType($phpClassNode, $phpMethodNode, $context, $annotation)
        );
    }

    protected function resolveMethodReturnType(
        PhpClassNode $classNode,
        PhpMethodNode $methodNode,
        TransformationContext $context,
        ?ParsedMethod $annotation
    ): TypeScriptNode {
        if ($annotation->returnType) {
            return $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->returnType,
                $classNode
            );
        }

        $returnType = $methodNode->getReturnType();

        if ($returnType) {
            return $this->transpilePhpTypeNodeToTypeScriptNodeAction->execute(
                $returnType,
                $classNode
            );
        }

        return new TypeScriptVoid();
    }

    protected function resolveMethodParameterType(
        PhpClassNode $classNode,
        PhpMethodNode $methodNode,
        PhpParameterNode $parameterNode,
        TransformationContext $context,
        ?ParsedNameAndType $annotation,
    ): TypeScriptParameter {
        $type = match (true) {
            $annotation !== null => $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->type,
                $classNode
            ),
            $parameterNode->hasType() => $this->transpilePhpTypeNodeToTypeScriptNodeAction->execute(
                $parameterNode->getType(),
                $classNode
            ),
            default => new TypeScriptUnknown(),
        };

        return new TypeScriptParameter(
            $parameterNode->getName(),
            $type,
            $parameterNode->isOptional()
        );
    }
}
