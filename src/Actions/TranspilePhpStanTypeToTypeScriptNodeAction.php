<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Exception;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ObjectShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ObjectShapeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptFunction;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIntersection;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptVoid;

class TranspilePhpStanTypeToTypeScriptNodeAction
{
    public function __construct(
        protected FindClassNameFqcnAction $findClassNameFqcnAction = new FindClassNameFqcnAction()
    ) {
    }

    public function execute(
        TypeNode $type,
        ?PhpClassNode $phpClassNode,
    ): TypeScriptNode {
        return match ($type::class) {
            IdentifierTypeNode::class => $this->identifierNode($type, $phpClassNode),
            ArrayTypeNode::class => $this->arrayTypeNode($type, $phpClassNode),
            GenericTypeNode::class => $this->genericNode($type, $phpClassNode),
            ArrayShapeNode::class, ObjectShapeNode::class => $this->arrayShapeNode($type, $phpClassNode),
            NullableTypeNode::class => $this->nullableNode($type, $phpClassNode),
            UnionTypeNode::class => $this->unionNode($type, $phpClassNode),
            IntersectionTypeNode::class => $this->intersectionNode($type, $phpClassNode),
            default => new TypeScriptUnknown(),
        };
    }

    protected function identifierNode(
        IdentifierTypeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptNode {
        if ($node->name === 'mixed') {
            return new TypeScriptAny();
        }

        if ($node->name === 'string' || $node->name === 'class-string') {
            return new TypeScriptString();
        }

        if ($node->name === 'float' || $node->name === 'double' || $node->name === 'int' || $node->name === 'integer') {
            return new TypeScriptNumber();
        }

        if ($node->name === 'bool' || $node->name === 'boolean' || $node->name === 'true' || $node->name === 'false') {
            return new TypeScriptBoolean();
        }

        if ($node->name === 'void') {
            return new TypeScriptVoid();
        }

        if ($node->name === 'array') {
            return new TypeScriptArray([]);
        }

        if ($node->name === 'callable') {
            return new TypeScriptFunction();
        }

        if ($node->name === 'null') {
            return new TypeScriptNull();
        }

        if ($node->name === 'self' || $node->name === 'static') {
            return new TypeReference(new ClassStringReference($phpClassNode->getName()));
        }

        if ($node->name === 'object') {
            return new TypeScriptObject([]);
        }

        if ($node->name === 'array-key') {
            return new TypeScriptUnion([
                new TypeScriptString(),
                new TypeScriptNumber(),
            ]);
        }

        if (class_exists($node->name) || interface_exists($node->name)) {
            return new TypeReference(new ClassStringReference($node->name));
        }

        if ($phpClassNode === null) {
            return new TypeScriptUnknown();
        }

        $referenced = $this->findClassNameFqcnAction->execute(
            $phpClassNode,
            $node->name
        );

        if (class_exists($referenced) || interface_exists($referenced)) {
            return new TypeReference(new ClassStringReference($referenced));
        }

        return new TypeScriptUnknown();
    }

    protected function arrayTypeNode(
        ArrayTypeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptNode {
        return new TypeScriptArray(
            [$this->execute($node->type, $phpClassNode)]
        );
    }

    protected function arrayShapeNode(
        ArrayShapeNode|ObjectShapeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptObject {
        return new TypeScriptObject(array_map(
            function (ArrayShapeItemNode|ObjectShapeItemNode $item) use ($phpClassNode) {
                return new TypeScriptProperty(
                    (string) $item->keyName,
                    $this->execute($item->valueType, $phpClassNode),
                    isOptional: $item->optional
                );
            },
            $node->items
        ));
    }

    protected function nullableNode(
        NullableTypeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptNode {
        $type = $this->execute($node->type, $phpClassNode);

        if (! $type instanceof TypeScriptUnion) {
            return new TypeScriptUnion([$type, new TypeScriptNull()]);
        }

        if ($type->contains(fn () => new TypeScriptNull())) {
            $type->types[] = new TypeScriptNull();
        }

        return $type;
    }

    protected function unionNode(
        UnionTypeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptUnion {
        return new TypeScriptUnion(array_map(
            fn (TypeNode $type) => $this->execute($type, $phpClassNode),
            $node->types
        ));
    }

    protected function intersectionNode(
        IntersectionTypeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptIntersection {
        return new TypeScriptIntersection(array_map(
            fn (TypeNode $type) => $this->execute($type, $phpClassNode),
            $node->types
        ));
    }

    protected function genericNode(
        GenericTypeNode $node,
        ?PhpClassNode $phpClassNode
    ): TypeScriptNode {
        if ($node->type->name === 'array' || $node->type->name === 'Array') {
            return $this->genericArrayNode($node, $phpClassNode);
        }

        $type = $this->execute($node->type, $phpClassNode);

        if ($type instanceof TypeScriptString) {
            return $type; // class-string<something> case
        }

        return new TypeScriptGeneric(
            $type,
            array_map(
                fn (TypeNode $type) => $this->execute($type, $phpClassNode),
                $node->genericTypes
            )
        );
    }

    private function genericArrayNode(GenericTypeNode $node, ?PhpClassNode $phpClassNode): TypeScriptGeneric|TypeScriptArray
    {
        $genericTypes = count($node->genericTypes);

        if ($genericTypes === 0) {
            return new TypeScriptArray([]);
        }

        if ($genericTypes === 1) {
            return new TypeScriptArray([$this->execute($node->genericTypes[0], $phpClassNode)]);
        }

        if ($genericTypes > 2) {
            throw new Exception('Invalid number of generic types for array');
        }

        $key = $this->execute($node->genericTypes[0], $phpClassNode);
        $value = $this->execute($node->genericTypes[1], $phpClassNode);

        if ($key instanceof TypeScriptNumber) {
            return new TypeScriptArray([$value]);
        }

        return new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [$key, $value,]
        );
    }
}
