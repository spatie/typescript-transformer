<?php

use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeExtension;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeWatchingTransformedProvider;
use Spatie\TypeScriptTransformer\Tests\TestSupport\UntransformableTransformer;
use Spatie\TypeScriptTransformer\TransformedProviders\TransformerProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Visitor\Common\ReplaceTypesVisitorClosure;

it('can add transformers as string and object', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformDirectories(__DIR__)
        ->transformer(UntransformableTransformer::class)
        ->transformer(new UntransformableTransformer())
        ->get();

    expect($config->transformers)->toHaveCount(2);
    expect($config->transformers[0])->toBeInstanceOf(UntransformableTransformer::class);
    expect($config->transformers[1])->toBeInstanceOf(UntransformableTransformer::class);
});

it('can prepend transformers before others', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformDirectories(__DIR__)
        ->transformer(EnumTransformer::class)
        ->prependTransformer(UntransformableTransformer::class)
        ->prependTransformer(new UntransformableTransformer())
        ->get();

    expect($config->transformers)->toHaveCount(3);
    expect($config->transformers[0])->toBeInstanceOf(UntransformableTransformer::class);
    expect($config->transformers[1])->toBeInstanceOf(UntransformableTransformer::class);
    expect($config->transformers[2])->toBeInstanceOf(EnumTransformer::class);
});

it('can replace a string transformer with another', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformDirectories(__DIR__)
        ->transformer(EnumTransformer::class)
        ->replaceTransformer(EnumTransformer::class, UntransformableTransformer::class)
        ->get();

    expect($config->transformers)->toHaveCount(1);
    expect($config->transformers[0])->toBeInstanceOf(UntransformableTransformer::class);
});

it('can replace an object transformer with another', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->transformDirectories(__DIR__)
        ->transformer(new EnumTransformer())
        ->replaceTransformer(EnumTransformer::class, new UntransformableTransformer())
        ->get();

    expect($config->transformers)->toHaveCount(1);
    expect($config->transformers[0])->toBeInstanceOf(UntransformableTransformer::class);
});

it('can add a type replacement with a TypeScriptNode', function () {
    $replacement = new TypeScriptString();

    $config = TypeScriptTransformerConfigFactory::create()
        ->replaceType('SomeClass', $replacement)
        ->get();

    expect($config->providedVisitorClosures)->toHaveCount(1);
    expect($config->providedVisitorClosures[0])->toBeInstanceOf(ReplaceTypesVisitorClosure::class);

    $reflector = new ReflectionProperty($config->providedVisitorClosures[0], 'typeReplacements');
    $typeReplacements = $reflector->getValue($config->providedVisitorClosures[0]);

    expect($typeReplacements['SomeClass'])->toBeInstanceOf(TypeScriptString::class);
});

it('can add a type replacement with a string that parses as PHP type', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->replaceType('SomeClass', 'string')
        ->get();

    expect($config->providedVisitorClosures)->toHaveCount(1);
    expect($config->providedVisitorClosures[0])->toBeInstanceOf(ReplaceTypesVisitorClosure::class);

    $reflector = new ReflectionProperty($config->providedVisitorClosures[0], 'typeReplacements');
    $typeReplacements = $reflector->getValue($config->providedVisitorClosures[0]);

    expect($typeReplacements['SomeClass'])->toBeInstanceOf(TypeScriptString::class);
});

it('can add a type replacement with a string that falls back to raw TypeScript', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->replaceType('SomeClass', 'Record<string, unknown>')
        ->get();

    expect($config->providedVisitorClosures)->toHaveCount(1);
    expect($config->providedVisitorClosures[0])->toBeInstanceOf(ReplaceTypesVisitorClosure::class);

    $reflector = new ReflectionProperty($config->providedVisitorClosures[0], 'typeReplacements');
    $typeReplacements = $reflector->getValue($config->providedVisitorClosures[0]);

    expect($typeReplacements['SomeClass'])->toBeInstanceOf(TypeScriptRaw::class);
});

it('can add an extension', function () {
    $extension = new FakeExtension();

    TypeScriptTransformerConfigFactory::create()
        ->extension($extension)
        ->get();

    expect($extension->enrichCalled)->toBeTrue();
});

it('cannot add a TransformerProvider class as provider', function () {
    TypeScriptTransformerConfigFactory::create()
        ->provider(TransformerProvider::class);
})->throws(Exception::class, "Please add transformers using the config's `transformer` method.");

it('cannot add a TransformerProvider instance as provider', function () {
    TypeScriptTransformerConfigFactory::create()
        ->provider(new TransformerProvider([], []));
})->throws(Exception::class, "Please add transformers using the config's `transformer` method.");

it('watches directories from transform directories, config paths, and WatchingTransformedProviders', function () {
    $watchingProvider = new FakeWatchingTransformedProvider(
        transformed: [],
        directoriesToWatch: ['/watched/dir']
    );

    $config = TypeScriptTransformerConfigFactory::create()
        ->transformDirectories('/transform/dir')
        ->configPath('/config/dir')
        ->provider($watchingProvider)
        ->get();

    expect($config->directoriesToWatch)->toContain('/transform/dir');
    expect($config->directoriesToWatch)->toContain('/config/dir');
    expect($config->directoriesToWatch)->toContain('/watched/dir');
});
