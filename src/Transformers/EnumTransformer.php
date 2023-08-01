<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use BackedEnum;
use ReflectionClass;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptEnum;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptExport;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;
use UnitEnum;

class EnumTransformer implements Transformer
{
    public function __construct(
        public bool $useNativeEnums = false,
    ) {
    }

    public function transform(
        ReflectionClass $reflectionClass,
        TransformationContext $context
    ): Transformed|Untransformable {
        if (! $this->isEnum($reflectionClass)) {
            return Untransformable::create();
        }

        $cases = $this->resolveCases($reflectionClass);

        return new Transformed(
            $this->useNativeEnums
                ? $this->transformAsNativeEnum($context->name, $cases)
                : $this->transformAsUnion($context->name, $cases),
            new ReflectionClassReference($reflectionClass),
            $context->name,
            $context->nameSpaceSegments,
        );
    }

    protected function isEnum(ReflectionClass $reflection): bool
    {
        return $reflection->isEnum();
    }

    protected function resolveCases(ReflectionClass $reflection): array
    {
        /** @var class-string<UnitEnum> $enumClass */
        $enumClass = $reflection->getName();

        $cases = [];

        foreach ($enumClass::cases() as $case) {
            $cases[$case->name] = $case instanceof BackedEnum
                ? $case->value
                : $case->name;
        }

        return $cases;
    }

    protected function transformAsNativeEnum(
        string $name,
        array $cases
    ): TypeScriptNode {
        return new TypeScriptExport(new TypeScriptEnum($name, $cases));
    }

    protected function transformAsUnion(
        string $name,
        array $cases
    ): TypeScriptNode {
        return new TypeScriptExport(new TypeScriptAlias(
            new TypeScriptIdentifier($name),
            new TypeScriptUnion(
                array_map(
                    fn (string $case) => new TypeScriptLiteral($case),
                    $cases,
                ),
            ),
        ));
    }
}
