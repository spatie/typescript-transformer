<?php

use MyCLabs\Enum\Enum;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('will check if an enum can be transformed', function () {
    $transformer = new MyclabsEnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $enum = new class('view') extends Enum {
        private const VIEW = 'view';
        private const EDIT = 'edit';
    };

    $noEnum = new class {
    };

    assertNotNull($transformer->transform(new ReflectionClass($enum), 'Enum'));
    assertNull($transformer->transform(new ReflectionClass($noEnum), 'Enum'));
});

it('can transform an enum into a type', function () {
    $transformer = new MyclabsEnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(false)
    );

    $enum = new class('view') extends Enum {
        private const VIEW = 'view';
        private const EDIT = 'edit';
    };

    $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

    assertEquals("'view' | 'edit'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertEquals('type', $type->keyword);
});

it('can transform an enum into an enum', function () {
    $transformer = new MyclabsEnumTransformer(
        TypeScriptTransformerConfig::create()->transformToNativeEnums(true)
    );

    $enum = new class('view') extends Enum {
        private const VIEW = 'view';
        private const EDIT = 'edit';
    };

    $type = $transformer->transform(new ReflectionClass($enum), 'Enum');

    assertEquals("'VIEW' = 'view', 'EDIT' = 'edit'", $type->transformed);
    assertTrue($type->missingSymbols->isEmpty());
    assertEquals('enum', $type->keyword);
});
