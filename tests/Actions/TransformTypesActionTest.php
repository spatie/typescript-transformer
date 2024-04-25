<?php

use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\HiddenAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;

it('can transform types', function () {
    $types = (new TransformTypesAction())->execute(
        [
            new EnumTransformer(),
            new AllClassTransformer(),
        ],
        [
            StringBackedEnum::class,
            SimpleClass::class,
        ]
    );

    expect($types)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Transformed::class);
});

it('will not transform untransformable types', function () {
    $types = (new TransformTypesAction())->execute(
        [
            new EnumTransformer(),
        ],
        [
            SimpleClass::class,
        ]
    );

    expect($types)->toBeEmpty();
});

it('can hide classes using an attribute', function () {
    $types = (new TransformTypesAction())->execute(
        [
            new AllClassTransformer(),
        ],
        [
            HiddenAttributedClass::class,
        ]
    );

    expect($types)->toBeEmpty();
});

it('will log errors when a type cannot be reflected', function () {
    $types = (new TransformTypesAction())->execute(
        [
            new AllClassTransformer(),
        ],
        [
            'NonExistentClass',
        ]
    );

    expect($types)->toBeEmpty();

    expect(TypeScriptTransformerLog::resolve()->errorMessages)
        ->toHaveCount(1);
});
