<?php

use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function Spatie\Snapshots\assertMatchesTextSnapshot;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeInterface;
use Spatie\TypeScriptTransformer\Transformers\InterfaceTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('will only convert interfaces', function () {
    $transformer = new InterfaceTransformer(
        TypeScriptTransformerConfig::create()
    );

    assertNotNull($transformer->transform(
        new ReflectionClass(DateTimeInterface::class),
        'State',
    ));

    assertNull($transformer->transform(
        new ReflectionClass(DateTime::class),
        'State',
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

    assertMatchesTextSnapshot($type->transformed);
});
