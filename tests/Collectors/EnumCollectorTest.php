<?php

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use Spatie\TypeScriptTransformer\Collectors\EnumCollector;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\BackedEnumWithoutAnnotation;

use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('collects backed enums', function () {
    $collector = new EnumCollector(
        TypeScriptTransformerConfig::create()->transformers([
            EnumTransformer::class,
        ])
    );

    $reflection = new ReflectionClass(BackedEnumWithoutAnnotation::class);
    $transformedType = $collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals(
        "'foo' | 'bar'",
        $transformedType->transformed,
    );
});
