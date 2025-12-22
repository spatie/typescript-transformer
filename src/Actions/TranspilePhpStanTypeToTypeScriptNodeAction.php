<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Exception;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
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
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;
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

        if ($node->name === 'string'
            || $node->name === 'class-string'
            || $node->name === 'interface-string'
            || $node->name === 'trait-string'
            || $node->name === 'callable-string'
            || $node->name === 'enum-string'
            || $node->name === 'lowercase-string'
            || $node->name === 'uppercase-string'
            || $node->name === 'literal-string'
            || $node->name === 'numeric-string'
            || $node->name === 'non-empty-string'
            || $node->name === 'non-empty-lowercase-string'
            || $node->name === 'non-empty-uppercase-string'
            || $node->name === 'truthy-string'
            || $node->name === 'non-falsy-string'
            || $node->name === 'non-empty-literal-string'
        ) {
            return new TypeScriptString();
        }

        if ($node->name === 'float'
            || $node->name === 'double'
            || $node->name === 'int'
            || $node->name === 'integer'
            || $node->name === 'positive-int'
            || $node->name === 'negative-int'
            || $node->name === 'non-positive-int'
            || $node->name === 'non-negative-int'
            || $node->name === 'non-zero-int'
            || $node->name === 'numeric'
        ) {
            return new TypeScriptNumber();
        }

        if ($node->name === 'bool' || $node->name === 'boolean' || $node->name === 'true' || $node->name === 'false') {
            return new TypeScriptBoolean();
        }

        if ($node->name === 'scalar') {
            return new TypeScriptUnion([
                new TypeScriptNumber(),
                new TypeScriptString(),
                new TypeScriptBoolean(),
            ]);
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

        if ($node->name === 'object') {
            return new TypeScriptObject([]);
        }

        if ($node->name === 'array-key') {
            return new TypeScriptUnion([
                new TypeScriptString(),
                new TypeScriptNumber(),
            ]);
        }

        $className = $this->resolveClass($node->name, $phpClassNode);

        if ($className) {
            return new TypeReference(new ClassStringReference($className));
        }

        return new TypeScriptUnknown();
    }

    protected function resolveClass(
        string $className,
        ?PhpClassNode $phpClassNode
    ): ?string {
        if ($className === 'self' || $className === 'static' || $className === '$this') {
            return $phpClassNode?->getName();
        }

        if (class_exists($className) || interface_exists($className)) {
            return $className;
        }

        if ($phpClassNode === null) {
            return null;
        }

        $referenced = $this->findClassNameFqcnAction->execute(
            $phpClassNode,
            $className
        );

        if (class_exists($referenced) || interface_exists($referenced)) {
            return $referenced;
        }

        return null;
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
        $properties = [];

        foreach ($node->items as $item) {
            $name = match ($item->keyName::class) {
                IdentifierTypeNode::class => $item->keyName->name,
                ConstExprStringNode::class => $item->keyName->value,
                ConstExprIntegerNode::class => (string) $item->keyName->value,
                default => null,
            };

            if ($name === null) {
                continue;
            }

            $properties[] = new TypeScriptProperty(
                $name,
                $this->execute($item->valueType, $phpClassNode),
                isOptional: $item->optional
            );
        }

        return new TypeScriptObject($properties);
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
        if ($node->type->name === 'array'
            || $node->type->name === 'Array'
            || $node->type->name === 'non-empty-array'
            || $node->type->name === 'list'
            || $node->type->name === 'non-empty-list'
        ) {
            return $this->genericArrayNode($node, $phpClassNode);
        }

        if ($node->type->name === 'int') {
            return new TypeScriptNumber();
        }

        if ($node->type->name === 'key-of' || $node->type->name === 'value-of') {
            return $this->keyOrValueOfGenericNode($node, $phpClassNode);
        }

        return $this->defaultGenericNode($node, $phpClassNode);
    }

    protected function genericArrayNode(GenericTypeNode $node, ?PhpClassNode $phpClassNode): TypeScriptGeneric|TypeScriptArray
    {
        $genericTypes = count($node->genericTypes);

        if ($genericTypes === 0) {
            return new TypeScriptArray([]);
        }

        if ($genericTypes === 1
            && $node->genericTypes[0] instanceof UnionTypeNode
        ) {
            return new TypeScriptArray(array_map(
                fn (TypeNode $type) => $this->execute($type, $phpClassNode),
                $node->genericTypes[0]->types
            ));
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
            [$key, $value]
        );
    }

    protected function keyOrValueOfGenericNode(GenericTypeNode $node, ?PhpClassNode $phpClassNode): TypeScriptNode
    {
        if (count($node->genericTypes) !== 1
            || ! $node->genericTypes[0] instanceof ConstTypeNode
            || ! $node->genericTypes[0]->constExpr instanceof ConstFetchNode
        ) {
            return $this->defaultGenericNode($node, $phpClassNode);
        }

        $constFetchNode = $node->genericTypes[0]->constExpr;
        $class = $this->resolveClass($constFetchNode->className, $phpClassNode);

        if ($class === null) {
            return $this->defaultGenericNode($node, $phpClassNode);
        }

        $array = $class::{$constFetchNode->name};

        $items = $node->type->name === 'key-of'
            ? array_keys($array)
            : array_values($array);

        return new TypeScriptUnion(array_map(
            fn (mixed $key) => new TypeScriptLiteral($key),
            $items
        ));
    }

    protected function defaultGenericNode(GenericTypeNode $node, ?PhpClassNode $phpClassNode): TypeScriptNode
    {
        $type = $this->execute($node->type, $phpClassNode);

        if ($type instanceof TypeScriptString) {
            return $type; // class-string<something> case
        }

        if (! ($type instanceof TypeReference || $type instanceof TypeScriptIdentifier)) {
            return new TypeScriptUnknown();
        }

        return new TypeScriptGeneric(
            $type,
            array_map(
                fn (TypeNode $type) => $this->execute($type, $phpClassNode),
                $node->genericTypes
            )
        );
    }
}
