<?php

use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\LaravelDto\LaravelDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\LaravelDto\LaravelOtherDto;
use Spatie\TypeScriptTransformer\Transformers\LaravelDtoTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

beforeEach(function () {
    $this->transformer = new LaravelDtoTransformer(
        resolve(TypeScriptTransformerConfig::class)
    );
});

it('can transform a dto', function () {
    $type = $this->transformer->transform(
        new ReflectionClass(LaravelDto::class),
        'FakeDto'
    );

    expect($type->transformed)->toMatchSnapshot();
    expect($type->typeReferences->has(LaravelOtherDto::class))->toBeTrue();
    expect($type->isInline)->toBeFalse();
});
