<?php

namespace Spatie\TypeScriptTransformer\Tests\TypeProcessors;

use Closure;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\TypeProcessors\ProcessesTypes;

class ProcessesTypesTest extends TestCase
{
    /** @test */
    public function it_supports_types()
    {
        $this->assertProcessed(
            'string',
            'string',
            fn (Type $type) => $type,
        );

        $this->assertProcessed(
            null,
            'string',
            fn (Type $type) => null,
        );

        $this->assertProcessed(
            'Array<int>',
            'Array<int>',
            fn (Type $type) => $type,
        );

        $this->assertProcessed(
            'string',
            'string|int',
            fn (Type $type) => $type instanceof Integer ? null : $type,
        );

        $this->assertProcessed(
            'int[]',
            'int[]',
            fn (Type $type) => $type,
        );

        $this->assertProcessed(
            'Collection<DateTime>',
            'Collection<DateTime>',
            fn (Type $type) => $type,
        );
    }

    private function assertProcessed(
        Type | string | null $expectedType,
        Type | string $initialType,
        Closure $closure
    ) {
        $processor = new class {
            use ProcessesTypes;

            public function run(Type $type, Closure $closure): ?Type
            {
                return $this->walk($type, $closure);
            }
        };

        $initialType = is_string($initialType)
            ? (new TypeResolver())->resolve($initialType)
            : $initialType;

        $expectedType = is_string($expectedType)
            ? (new TypeResolver())->resolve($expectedType)
            : $expectedType;

        $found = $processor->run($initialType, $closure);

        $this->assertEquals($expectedType, $found);
    }
}
