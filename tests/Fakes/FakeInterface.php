<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes;

interface FakeInterface
{
    public function testFunction(string $input, array $output): int;

    public function anotherTestFunction(): bool;
}
