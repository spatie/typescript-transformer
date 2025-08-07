<?php

namespace Spatie\TypeScriptTransformer\Transformers\EnumProviders;

use InvalidArgumentException;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpEnumCaseNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpEnumNode;

class PhpEnumProvider implements EnumProvider
{
    public function isEnum(PhpClassNode $phpClassNode): bool
    {
        return $phpClassNode->isEnum();
    }

    public function isValidUnion(PhpClassNode $phpClassNode): bool
    {
        return $phpClassNode instanceof PhpEnumNode && $phpClassNode->isBacked();
    }

    /**
     * @return array<int, array{name: string, value:string|int|null}>
     */
    public function resolveCases(PhpClassNode|PhpEnumNode $phpClassNode): array
    {
        if (! $phpClassNode instanceof PhpEnumNode) {
            throw new InvalidArgumentException('Expected instance of PhpEnumNode.');
        }

        return array_map(
            fn (PhpEnumCaseNode $case) => [
                'name' => $case->getName(),
                'value' => $case->getValue(),
            ],
            array_values($phpClassNode->getCases())
        );
    }
}
