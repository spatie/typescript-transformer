<?php

use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\OptionalAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptLocationAttributedClass;

it('can create a context from reflection', function () {
    $reflection = new ReflectionClass(SimpleClass::class);

    $context = TransformationContext::createFromReflection($reflection);

    expect($context->name)->toBe('SimpleClass');
    expect($context->nameSpaceSegments)->toBe(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']);
    expect($context->optional)->toBeFalse();
});

it('can make a class optional by attribute in its context', function () {
    $reflection = new ReflectionClass(OptionalAttributedClass::class);

    $context = TransformationContext::createFromReflection($reflection);

    expect($context->optional)->toBeTrue();
});

it('can set the name by attribute', function () {
    $reflection = new ReflectionClass(TypeScriptAttributedClass::class);

    $context = TransformationContext::createFromReflection($reflection);

    expect($context->name)->toBe('JustAnotherName');
});

it('can set the location by attribute', function () {
    $reflection = new ReflectionClass(TypeScriptLocationAttributedClass::class);

    $context = TransformationContext::createFromReflection($reflection);

    expect($context->nameSpaceSegments)->toBe(['App', 'Here']);
});
