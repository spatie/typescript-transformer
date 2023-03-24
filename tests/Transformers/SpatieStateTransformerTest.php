<?php

use Spatie\TypeScriptTransformer\Tests\FakeClasses\MyclabsEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\SpatieEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\States\ChildState;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\States\State;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnum;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\SpatieEnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\SpatieStateTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

it('will only convert enums', function () {
    $transformer = new SpatieStateTransformer(
        TypeScriptTransformerConfig::create()->transformer(SpatieStateTransformer::class)
    );

    assertTrue($transformer->canTransform(
        new ReflectionClass(State::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(ChildState::class),
    ));

    assertFalse($transformer->canTransform(
        new ReflectionClass(DateTime::class),
    ));
});

it('can transform an state as union', function () {
    $transformer = new SpatieStateTransformer(
        TypeScriptTransformerConfig::create()->transformer(SpatieStateTransformer::class, ['as_native_enum' => false])
    );

    $type = $transformer->transform(
        new ReflectionClass(State::class),
    );

    expect($type)
        ->inline->toBeFalse()
        ->typeReferences->toBeEmpty()
        ->toString()->toBe("type State = 'child' | 'other_child';");
});

