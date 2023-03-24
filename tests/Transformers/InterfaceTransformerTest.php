<?php

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use function Spatie\Snapshots\assertMatchesTextSnapshot;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeInterface;
use Spatie\TypeScriptTransformer\Transformers\InterfaceTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('will only convert interfaces', function () {
    $transformer = new InterfaceTransformer(
        TypeScriptTransformerConfig::create()
    );

    assertTrue($transformer->canTransform(
        new ReflectionClass(DateTimeInterface::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(DateTime::class),
    ));
});

it('will replace methods', function () {
    $transformer = new InterfaceTransformer(
        TypeScriptTransformerConfig::create()
    );

    $type = $transformer->transform(
        new ReflectionClass(FakeInterface::class),
        'State',
    );

    assertMatchesTextSnapshot($type->toString());
});
