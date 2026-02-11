<?php

use Spatie\TypeScriptTransformer\Data\WritingContext;

it('resolves a reference from the map', function () {
    $context = new WritingContext([
        'key' => 'MyType',
    ]);

    expect($context->resolveReference('key'))->toBe('MyType');
});

it('returns undefined for unknown references', function () {
    $context = new WritingContext([]);

    expect($context->resolveReference('unknown'))->toBe('undefined');
});


it('returns dotted references as-is when nothing is shadowed', function () {
    $context = new WritingContext([
        'key' => 'App.Models.User',
    ]);

    $context->pushNamespace('App');
    $context->pushNamespace('Http');
    $context->pushNamespace('Controllers');

    expect($context->resolveReference('key'))->toBe('App.Models.User');
});

it('strips a shadowed first segment', function () {
    $context = new WritingContext([
        'key' => 'App.Domain.Data.ErrorData',
    ]);

    $context->pushNamespace('App');
    $context->pushNamespace('Http');
    $context->pushNamespace('App');
    $context->pushNamespace('ViewModels');

    expect($context->resolveReference('key'))->toBe('Domain.Data.ErrorData');
});

it('does not strip when the name only appears at the root of the path', function () {
    $context = new WritingContext([
        'key' => 'App.Scope.Data.SomeType',
    ]);

    $context->pushNamespace('App');
    $context->pushNamespace('Scope');
    $context->pushNamespace('Controllers');
    $context->pushNamespace('Scope');

    expect($context->resolveReference('key'))->toBe('App.Scope.Data.SomeType');
});

it('strips multiple shadowed segments', function () {
    $context = new WritingContext([
        'key' => 'A.B.C.MyType',
    ]);

    $context->pushNamespace('A');
    $context->pushNamespace('B');
    $context->pushNamespace('A');
    $context->pushNamespace('B');

    // A is shadowed (index 2) -> strip -> B.C.MyType
    // B is shadowed (index 3) -> strip -> C.MyType
    expect($context->resolveReference('key'))->toBe('C.MyType');
});

it('never strips to fewer than one segment', function () {
    $context = new WritingContext([
        'key' => 'A.B',
    ]);

    $context->pushNamespace('X');
    $context->pushNamespace('A');
    $context->pushNamespace('B');

    // A shadowed -> strip -> B
    // B shadowed -> but only 1 segment left, stop
    expect($context->resolveReference('key'))->toBe('B');
});

it('does not strip non-dotted references even inside namespaces', function () {
    $context = new WritingContext([
        'key' => 'SimpleType',
    ]);

    $context->pushNamespace('App');
    $context->pushNamespace('SimpleType');

    expect($context->resolveReference('key'))->toBe('SimpleType');
});

it('respects pop restoring the previous namespace', function () {
    $context = new WritingContext([
        'key' => 'App.Models.User',
    ]);

    $context->pushNamespace('App');
    $context->pushNamespace('Http');
    $context->pushNamespace('App'); // shadows root App

    expect($context->resolveReference('key'))->toBe('Models.User');

    $context->popNamespace(); // remove inner App

    // Back to App.Http - no shadow anymore
    expect($context->resolveReference('key'))->toBe('App.Models.User');
});
