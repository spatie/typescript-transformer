<?php

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\String_;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Exceptions\InvalidDefaultTypeReplacer;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\Types\TypeScriptType;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

it('can create transformers', function () {
    $config = TypeScriptTransformerConfig::create()->transformers([
        MyclabsEnumTransformer::class,
    ]);

    assertEquals([new MyclabsEnumTransformer($config)], $config->getTransformers());
});

it('can create transformers with constructor', function () {
    $config = TypeScriptTransformerConfig::create()->transformers([
        DtoTransformer::class,
    ]);

    assertEquals([new DtoTransformer($config)], $config->getTransformers());
});

it('will check if a class property replacement class exists', function () {
    $this->expectException(InvalidDefaultTypeReplacer::class);

    $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
        'fake-class' => 'string',
    ]);

    $config->getDefaultTypeReplacements();
});

it('can use a php type in a class property replacer', function () {
    $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
        DateTime::class => 'array<string, string>',
    ]);

    assertEquals(
        [DateTime::class => new Array_(new String_(), new String_())],
        $config->getDefaultTypeReplacements()
    );
});

it('can use a typescript type in a class property replacer', function () {
    $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
        Dto::class => new TypeScriptType('any'),
    ]);

    assertEquals(
        [Dto::class => new TypeScriptType('any')],
        $config->getDefaultTypeReplacements()
    );
});

it('can use a php dodumenter type in a class property replacer', function () {
    $config = TypeScriptTransformerConfig::create()->defaultTypeReplacements([
        Dto::class => new String_(),
    ]);

    assertEquals(
        [Dto::class => new String_()],
        $config->getDefaultTypeReplacements()
    );
});

it('can handle string as compactor_prefixes parameter', function () {
    $config = TypeScriptTransformerConfig::create()->compactorPrefixes('asdf.asdf');

    assertEquals(['asdf.asdf'], $config->getCompactorPrefixes());
});
