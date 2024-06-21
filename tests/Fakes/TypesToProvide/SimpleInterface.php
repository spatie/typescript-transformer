<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide;

interface SimpleInterface
{
    public function withoutParametersAndReturnType();

    public function withReturnType(): string;

    /**
     * @return array<string>
     */
    public function withAnnotatedReturnType(): array;

    public function withParameters(string $param1, int $param2): void;

    public function withOptionalParameters(string $param1, int $param2 = 5): void;

    /**
     * @param array<string> $param1
     * @param array<bool> $param2
     */
    public function withAnnotatedParameters(array $param1, array $param2): void;
}
