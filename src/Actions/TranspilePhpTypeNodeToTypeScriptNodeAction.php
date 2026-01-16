<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpIntersectionTypeNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpNamedTypeNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpTypeNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpUnionTypeNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIntersection;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUndefined;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

class TranspilePhpTypeNodeToTypeScriptNodeAction
{
    public function execute(
        PhpTypeNode $phpTypeNode,
        PhpClassNode $phpClassNode,
    ): TypeScriptNode {
        $type = $this->resolveType($phpTypeNode, $phpClassNode);

        if (
            ! $phpTypeNode->allowsNull()
            || $type instanceof TypeScriptAny
            || $type instanceof TypeScriptNull) {
            return $type;
        }

        if ($type instanceof TypeScriptUnion && $type->contains(fn (TypeScriptNode $node) => $node instanceof TypeScriptNull)) {
            return $type;
        }

        if ($type instanceof TypeScriptUnion) {
            $type->types[] = new TypeScriptNull();

            return $type;
        }

        return new TypeScriptUnion([$type, new TypeScriptNull()]);
    }

    protected function resolveType(
        PhpTypeNode $phpTypeNode,
        PhpClassNode $phpClassNode,
    ): TypeScriptNode {
        return match ($phpTypeNode::class) {
            PhpNamedTypeNode::class => $this->namedType($phpTypeNode, $phpClassNode),
            PhpUnionTypeNode::class => $this->unionType($phpTypeNode, $phpClassNode),
            PhpIntersectionTypeNode::class => $this->intersectionType($phpTypeNode, $phpClassNode),
            default => new TypeScriptUndefined(),
        };
    }

    protected function namedType(
        PhpNamedTypeNode $type,
        PhpClassNode $phpClassNode,
    ): TypeScriptNode {
        if ($type->getName() === 'string') {
            return new TypeScriptString();
        }

        if ($type->getName() === 'float' || $type->getName() === 'int') {
            return new TypeScriptNumber();
        }

        if ($type->getName() === 'bool' || $type->getName() === 'true' || $type->getName() === 'false') {
            return new TypeScriptBoolean();
        }

        if ($type->getName() === 'array') {
            return new TypeScriptArray([]);
        }

        if ($type->getName() === 'null') {
            return new TypeScriptNull();
        }

        if ($type->getName() === 'mixed') {
            return new TypeScriptAny();
        }

        if ($type->getName() === 'self' || $type->getName() === 'static') {
            return new TypeReference(new ClassStringReference($phpClassNode->getName()));
        }

        if ($type->getName() === 'object') {
            return new TypeScriptObject([]);
        }

        if ($type->getName() === 'void') {
            return new TypeScriptVoid();
        }

        if (class_exists($type->getName()) || interface_exists($type->getName())) {
            return new TypeReference(new ClassStringReference($type->getName()));
        }

        return new TypeScriptUnknown();
    }

    protected function unionType(
        PhpUnionTypeNode $type,
        PhpClassNode $phpClassNode,
    ): TypeScriptNode {
        return new TypeScriptUnion(array_map(
            fn (PhpTypeNode $type) => $this->resolveType($type, $phpClassNode),
            $type->getTypes()
        ));
    }

    protected function intersectionType(
        PhpIntersectionTypeNode $type,
        PhpClassNode $classNode,
    ): TypeScriptNode {
        return new TypeScriptIntersection(array_map(
            fn (PhpTypeNode $type) => $this->resolveType($type, $classNode),
            $type->getTypes()
        ));
    }
}
