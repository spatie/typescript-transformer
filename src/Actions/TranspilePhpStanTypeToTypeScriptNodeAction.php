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
use ReflectionClass;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAny;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptBoolean;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptFunction;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIntersection;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnknown;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptVoid;

class TranspilePhpStanTypeToTypeScriptNodeAction
{
    public function __construct(
        protected FindClassNameFqcnAction $findClassNameFqcnAction = new FindClassNameFqcnAction()
    ) {
    }

    public function execute(
        TypeNode $type,
        ?ReflectionClass $reflectionClass,
    ): TypeScriptNode {
        return match ($type::class) {
            IdentifierTypeNode::class => $this->identifierNode($type, $reflectionClass),
            ArrayTypeNode::class => $this->arrayTypeNode($type, $reflectionClass),
            GenericTypeNode::class => $this->genericNode($type, $reflectionClass),
            ArrayShapeNode::class, ObjectShapeNode::class => $this->arrayShapeNode($type, $reflectionClass),
            NullableTypeNode::class => $this->nullableNode($type, $reflectionClass),
            UnionTypeNode::class => $this->unionNode($type, $reflectionClass),
            IntersectionTypeNode::class => $this->intersectionNode($type, $reflectionClass),
            default => new TypeScriptUnknown(),
        };
    }

    protected function identifierNode(
        IdentifierTypeNode $node,
        ?ReflectionClass $reflectionClass
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
            return new TypeReference(new ClassStringReference($reflectionClass->getName()));
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

        if ($reflectionClass === null) {
            return new TypeScriptUnknown();
        }

        $referenced = $this->findClassNameFqcnAction->execute(
            $reflectionClass,
            $node->name
        );

        if (class_exists($referenced) || interface_exists($referenced)) {
            return new TypeReference(new ClassStringReference($referenced));
        }

        return new TypeScriptUnknown();
    }

    protected function arrayTypeNode(
        ArrayTypeNode $node,
        ?ReflectionClass $reflectionClass
    ): TypeScriptNode {
        return new TypeScriptArray(
            [$this->execute($node->type, $reflectionClass)]
        );
    }

    protected function arrayShapeNode(
        ArrayShapeNode|ObjectShapeNode $node,
        ?ReflectionClass $reflectionClass
    ): TypeScriptObject {
        return new TypeScriptObject(array_map(
            function (ArrayShapeItemNode|ObjectShapeItemNode $item) use ($reflectionClass) {
                return new TypeScriptProperty(
                    (string) $item->keyName,
                    $this->execute($item->valueType, $reflectionClass),
                    isOptional: $item->optional
                );
            },
            $node->items
        ));
    }

    protected function nullableNode(
        NullableTypeNode $node,
        ?ReflectionClass $reflectionClass
    ): TypeScriptNode {
        $type = $this->execute($node->type, $reflectionClass);

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
        ?ReflectionClass $reflectionClass
    ): TypeScriptUnion {
        return new TypeScriptUnion(array_map(
            fn (TypeNode $type) => $this->execute($type, $reflectionClass),
            $node->types
        ));
    }

    protected function intersectionNode(
        IntersectionTypeNode $node,
        ?ReflectionClass $reflectionClass
    ): TypeScriptIntersection {
        return new TypeScriptIntersection(array_map(
            fn (TypeNode $type) => $this->execute($type, $reflectionClass),
            $node->types
        ));
    }

    protected function genericNode(
        GenericTypeNode $node,
        ?ReflectionClass $reflectionClass
    ): TypeScriptNode {
        if ($node->type->name === 'array' || $node->type->name === 'Array') {
            return $this->genericArrayNode($node, $reflectionClass);
        }

        $type = $this->execute($node->type, $reflectionClass);

        if ($type instanceof TypeScriptString) {
            return $type; // class-string<something> case
        }

        return new TypeScriptGeneric(
            $type,
            array_map(
                fn (TypeNode $type) => $this->execute($type, $reflectionClass),
                $node->genericTypes
            )
        );
    }

    private function genericArrayNode(GenericTypeNode $node, ?ReflectionClass $reflectionClass): TypeScriptGeneric|TypeScriptArray
    {
        $genericTypes = count($node->genericTypes);

        if ($genericTypes === 0) {
            return new TypeScriptArray([]);
        }

        if ($genericTypes === 1) {
            return new TypeScriptArray([$this->execute($node->genericTypes[0], $reflectionClass)]);
        }

        if ($genericTypes > 2) {
            throw new Exception('Invalid number of generic types for array');
        }

        $key = $this->execute($node->genericTypes[0], $reflectionClass);
        $value = $this->execute($node->genericTypes[1], $reflectionClass);

        if ($key instanceof TypeScriptNumber) {
            return new TypeScriptArray([$value]);
        }

        return new TypeScriptGeneric(
            new TypeScriptIdentifier('Record'),
            [$key, $value,]
        );
    }
}
