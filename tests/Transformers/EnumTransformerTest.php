<?php

use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\EmptyEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\IntBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\UnitEnum;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;

it('will only convert enums', function () {
    expect(transformSingle(StringBackedEnum::class, new EnumTransformer()))->toBeInstanceOf(Transformed::class);
    expect(transformSingle(DateTime::class, new EnumTransformer()))->toBeInstanceOf(Untransformable::class);
});

it('does not transform a unit enum when using unit enums', function () {
    expect(transformSingle(UnitEnum::class, new EnumTransformer()))->toBeInstanceOf(Untransformable::class);
});

it('can transform an unit backed enum into a native enum', function () {
    expect(classesToTypeScript([UnitEnum::class], new EnumTransformer(useUnionEnums: false)))
        ->toBe(<<<TS
export enum UnitEnum {
    JOHN,
    PAUL,
    GEORGE,
    RINGO,
}

TS);
});

it('can transform an int backed enum into a union enum', function () {
    expect(classesToTypeScript([IntBackedEnum::class], new EnumTransformer()))
        ->toBe('export type IntBackedEnum = 1 | 2 | 3 | 4;'.PHP_EOL);
});

it('can transform an int backed enum into a native enum', function () {
    expect(classesToTypeScript([IntBackedEnum::class], new EnumTransformer(useUnionEnums: false)))
        ->toBe(<<<TS
export enum IntBackedEnum {
    John = 1,
    Paul = 2,
    George = 3,
    Ringo = 4,
}

TS);
});

it('can transform a string backed enum into a union enum', function () {
    expect(classesToTypeScript([StringBackedEnum::class], new EnumTransformer()))
        ->toBe('export type StringBackedEnum = "john" | "paul" | "george" | "ringo";' . PHP_EOL);
});

it('can transform a string backed enum into a native enum', function () {
    expect(classesToTypeScript([StringBackedEnum::class], new EnumTransformer(useUnionEnums: false)))
        ->toBe(
            <<<TS
export enum StringBackedEnum {
    John = 'john',
    Paul = 'paul',
    George = 'george',
    Ringo = 'ringo',
}

TS
        );
});

it('will not transform empty enums', function () {
    $transformer = new EnumTransformer();

    $transformed = $transformer->transform(
        $enum = PhpClassNode::fromClassString(EmptyEnum::class),
        TransformationContext::createFromPhpClass($enum),
    );

    expect($transformed)->toBeInstanceOf(Untransformable::class);
});
