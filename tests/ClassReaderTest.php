<?php

use Spatie\TypeScriptTransformer\ClassReader;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    $this->reader = new ClassReader();
});

it('non transformable case', function () {
    $fake = new class {
    };

    ['transformable' => $transformable] = $this->reader->forClass(
        new ReflectionClass($fake)
    );

    assertFalse($transformable);
});

it('default case', function () {
    /**
     * @typescript
     */
    $fake = new class {
    };

    ['name' => $name] = $this->reader->forClass(
        new ReflectionClass($fake)
    );

    assertStringContainsString('class@anonymous', $name);
});

it('default file case', function () {
    /**
     * @typescript OtherEnum
     */
    $fake = new class {
    };

    ['name' => $name] = $this->reader->forClass(
        new ReflectionClass($fake)
    );

    assertEquals('OtherEnum', $name);
});

it('will resolve the transformer', function () {
    /**
     * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer
     */
    $fake = new class {
    };

    assertEquals('\\' . MyclabsEnumTransformer::class, $this->reader->forClass(
        new ReflectionClass($fake)
    )['transformer']);
});

it('inline case', function () {
    /**
     * @typescript
     * @typescript-inline
     */
    $fake = new class {
    };

    ['inline' => $inline] = $this->reader->forClass(
        new ReflectionClass($fake)
    );

    assertTrue($inline);
});
