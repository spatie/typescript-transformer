<?php

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Integer;
use Spatie\TypeScriptTransformer\TypeProcessors\ProcessesTypes;
use function PHPUnit\Framework\assertEquals;

function assertProcessed(
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

    assertEquals($expectedType, $found);
}

it('supports types', function () {
    assertProcessed(
        'string',
        'string',
        fn (Type $type) => $type,
    );

    assertProcessed(
        null,
        'string',
        fn (Type $type) => null,
    );

    assertProcessed(
        'Array<int>',
        'Array<int>',
        fn (Type $type) => $type,
    );

    assertProcessed(
        'string',
        'string|int',
        fn (Type $type) => $type instanceof Integer ? null : $type,
    );

    assertProcessed(
        'int[]',
        'int[]',
        fn (Type $type) => $type,
    );

    assertProcessed(
        'Collection<DateTime>',
        'Collection<DateTime>',
        fn (Type $type) => $type,
    );
});
