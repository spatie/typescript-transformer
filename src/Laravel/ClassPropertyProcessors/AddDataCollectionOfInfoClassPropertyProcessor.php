<?php

namespace Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

class AddDataCollectionOfInfoClassPropertyProcessor implements ClassPropertyProcessor
{
    protected Visitor $visitor;

    public function __construct()
    {
        $this->buildVisitor();
    }

    public function execute(ReflectionProperty $reflection, ?TypeNode $annotation, TypeScriptProperty $property): ?TypeScriptProperty
    {
        $attributes = $reflection->getAttributes('Spatie\LaravelData\Attributes\DataCollectionOf');

        if (empty($attributes)) {
            return $property;
        }

        $attribute = $attributes[0];

        $metadata = [
            'dataClass' => $attribute->getArguments()[0],
        ];

        $property->type = $this->visitor->execute($property->type, $metadata);

        return $property;
    }

    protected function buildVisitor(): void
    {
        $this->visitor = Visitor::create()->before(function (TypeReference $node, &$metadata) {
            if (
                $node->reference instanceof ClassStringReference
                && is_a($node->reference->classString, 'Spatie\LaravelData\DataCollection', true)
            ) {
                return VisitorOperation::replace(new TypeScriptGeneric(
                    $node,
                    [
                        new TypeReference(new ClassStringReference($metadata['dataClass'])),
                    ]
                ));
            }

            return $node;
        }, [TypeReference::class]);
    }
}
