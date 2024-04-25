<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\EnumProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\PhpEnumProvider;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptEnum;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnion;

class EnumTransformer implements Transformer
{
    public function __construct(
        public bool $useUnionEnums = true,
        public EnumProvider $enumProvider = new PhpEnumProvider()
    ) {
    }

    public function transform(
        ReflectionClass $reflectionClass,
        TransformationContext $context
    ): Transformed|Untransformable {
        if (! $this->enumProvider->isEnum($reflectionClass)) {
            return Untransformable::create();
        }

        if ($this->useUnionEnums === true && ! $this->enumProvider->isValidUnion($reflectionClass)) {
            return Untransformable::create();
        }

        $cases = $this->enumProvider->resolveCases($reflectionClass);

        return new Transformed(
            $this->useUnionEnums
                ? $this->transformAsUnion($context->name, $cases)
                : $this->transformAsNativeEnum($context->name, $cases),
            new ReflectionClassReference($reflectionClass),
            $context->nameSpaceSegments,
            true,
        );
    }

    protected function transformAsNativeEnum(
        string $name,
        array $cases
    ): TypeScriptNode {
        return new TypeScriptEnum($name, $cases);
    }

    protected function transformAsUnion(
        string $name,
        array $cases
    ): TypeScriptNode {
        return new TypeScriptAlias(
            new TypeScriptIdentifier($name),
            new TypeScriptUnion(
                array_map(
                    fn (array $case) => new TypeScriptLiteral($case['value']),
                    $cases,
                ),
            ),
        );
    }
}
