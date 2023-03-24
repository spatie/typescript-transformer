<?php

use Spatie\TypeScriptTransformer\Actions\ResolveTransformedAction;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Structures\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Structures\TypeReference;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptInlineAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\NativeEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\StringBackedEnum;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

it('will not collect non annotated classes', function () {
    $class = new class('a') extends Enum {
        const A = 'a';
    };

    $reflection = new ReflectionClass($class);

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)->toBeNull();
});

it('will collect annotated classes', function () {
    $reflection = new ReflectionClass(
        StringBackedEnum::class
    );

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(NativeEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn(StringBackedEnum::class))
        ->typeReferences->toBeEmpty()
        ->inline->toBeFalse()
        ->toString()->toEqual("type StringBackedEnum = 'js' | 'php';");
});

it('will collect annotated classes and use the given name', function () {
    /** @typescript EnumTransformed */
    $class = new class('a') extends Enum {
        const A = 'a';
    };

    $reflection = new ReflectionClass(
        $class
    );

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn($class::class, 'EnumTransformed'))
        ->typeReferences->toBeEmpty()
        ->inline->toBeFalse()
        ->toString()->toEqual("type EnumTransformed = 'a' | 'yes' | 'no';");
});

it('will read overwritten transformers', function () {
    /**
     * @typescript DtoTransformed
     * @typescript-transformer \Spatie\TypeScriptTransformer\Transformers\DtoTransformer
     */
    $class = new class('a') extends Enum {
        const A = 'a';

        public int $an_integer;
    };

    $reflection = new ReflectionClass(
        $class
    );

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn($class::class, 'DtoTransformed'))
        ->typeReferences->toBeEmpty()
        ->inline->toBeFalse()
        ->toString()->toEqual(<<<TS
type DtoTransformed = {
an_integer: number;
};
TS
        );
});

it('will throw an exception if a transformer is not found', function () {
    /** @typescript */
    $class = new class {
    };

    $reflection = new ReflectionClass(
        $class
    );

    (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);
})->throws(TransformerNotFound::class);

it('will collect classes with attributes', function () {
    $reflection = new ReflectionClass(WithTypeScriptAttribute::class);

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn(WithTypeScriptAttribute::class))
        ->typeReferences->toBeEmpty()
        ->inline->toBeFalse()
        ->toString()->toEqual("type WithTypeScriptAttribute = 'a' | 'b';");
});

it('will collect attribute overwritten transformers', function () {
    $reflection = new ReflectionClass(WithTypeScriptTransformerAttribute::class);

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn(WithTypeScriptTransformerAttribute::class))
        ->typeReferences->toBeEmpty()
        ->inline->toBeFalse()
        ->toString()->toEqual(<<<TS
type WithTypeScriptTransformerAttribute = {
an_int: number;
};
TS
        );
});

it('will collect classes with already transformed attributes', function () {
    $reflection = new ReflectionClass(WithAlreadyTransformedAttributeAttribute::class);

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn(WithAlreadyTransformedAttributeAttribute::class))
        ->typeReferences->toBeEmpty()
        ->inline->toBeFalse()
        ->toString()->toEqual('type WithAlreadyTransformedAttributeAttribute = {an_int:number;a_bool:boolean;};');
});

it('can inline collected classes with annotations', function () {
    $reflection = new ReflectionClass(WithTypeScriptInlineAttribute::class);

    $transformed = (new ResolveTransformedAction(
        TypeScriptTransformerConfig::create()->transformer(MyclabsEnumTransformer::class)
    ))->execute($reflection);

    expect($transformed)
        ->toBeInstanceOf(Transformed::class)
        ->name->toEqual(TypeReference::fromFqcn(WithTypeScriptInlineAttribute::class))
        ->typeReferences->toBeEmpty()
        ->inline->toBeTrue()
        ->toString()->toEqual("'a' | 'b'");
});

it('can inline collected classes with attributes', function () {
    /**
     * @typescript
     * @typescript-inline
     */
    $class = new class('a') extends Enum {
        const A = 'a';
    };

    $transformedType = $this->collector->getTransformedType(new ReflectionClass($class));

    assertNotNull($transformedType);
    assertTrue($transformedType->isInline);
});

it('will will throw an exception with non existing transformers', function () {
    $this->expectException(InvalidTransformerGiven::class);
    $this->expectDeprecationMessageMatches("/does not exist!/");

    /**
     * @typescript             DtoTransformed
     * @typescript-transformer FAKE
     */
    $class = new class('a') extends Enum {
        const A = 'a';

        public int $an_integer;
    };

    $this->collector->getTransformedType(new ReflectionClass($class));
});

it('will will throw an exception with class that does not implement transformer', function () {
    $this->expectException(InvalidTransformerGiven::class);
    $this->expectDeprecationMessageMatches("/does not implement the Transformer interface!/");

    /**
     * @typescript-transformer \Spatie\TypeScriptTransformer\Structures\TransformedType
     */
    $class = new class {
    };

    $this->collector->getTransformedType(new ReflectionClass($class));
});
