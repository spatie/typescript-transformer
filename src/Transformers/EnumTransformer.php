<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\PhpClassReference;
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\EnumProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumProviders\PhpEnumProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptEnum;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptLiteral;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;

class EnumTransformer implements Transformer
{
    public function __construct(
        public bool $useUnionEnums = true,
        public EnumProvider $enumProvider = new PhpEnumProvider()
    ) {
    }

    public function transform(
        PhpClassNode $phpClassNode,
        TransformationContext $context
    ): Transformed|Untransformable {
        if (! $this->enumProvider->isEnum($phpClassNode)) {
            return Untransformable::create();
        }

        if ($this->useUnionEnums === true && ! $this->enumProvider->isValidUnion($phpClassNode)) {
            return Untransformable::create();
        }

        $cases = $this->enumProvider->resolveCases($phpClassNode);

        if(count($cases) === 0) {
            return Untransformable::create();
        }

        return new Transformed(
            $this->useUnionEnums
                ? $this->transformAsUnion($context->name, $cases)
                : $this->transformAsNativeEnum($context->name, $cases),
            new PhpClassReference($phpClassNode),
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
