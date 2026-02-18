<?php

use Spatie\TypeScriptTransformer\Actions\TransformTypesAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\HiddenAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;

it('can transform types', function () {
    $types = (new TransformTypesAction())->execute(
        [
            new EnumTransformer(),
            new AllClassTransformer(),
        ],
        [
            PhpClassNode::fromClassString(StringBackedEnum::class),
            PhpClassNode::fromClassString(SimpleClass::class),
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
            PhpClassNode::fromClassString(SimpleClass::class),
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
            PhpClassNode::fromClassString(HiddenAttributedClass::class),
        ]
    );

    expect($types)->toBeEmpty();
});
