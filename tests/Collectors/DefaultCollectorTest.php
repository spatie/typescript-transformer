<?php

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use Spatie\TypeScriptTransformer\Collectors\DefaultCollector;
use Spatie\TypeScriptTransformer\Exceptions\InvalidTransformerGiven;
use Spatie\TypeScriptTransformer\Exceptions\TransformerNotFound;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithAlreadyTransformedAttributeAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptInlineAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Attributes\WithTypeScriptTransformerAttribute;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

beforeEach(function () {
    $this->config = TypeScriptTransformerConfig::create()->transformers([
        MyclabsEnumTransformer::class,
    ]);

    $this->collector = new DefaultCollector($this->config);
});

it('will not collect non annotated classes', function () {
    $class = new class('a') extends Enum {
        const A = 'a';
    };

    $reflection = new ReflectionClass(
        $class
    );

    assertNull($this->collector->getTransformedType($reflection));
});

it('will collect backed enums', function () {
    $collector = new DefaultCollector(
        TypeScriptTransformerConfig::create()->transformers([
            EnumTransformer::class,
        ])
    );
    
    enum TestEnum: string
    {
        case Foo = 'foo';
        case Bar = 'bar';
    };

    $reflection = new ReflectionClass(TestEnum::class);
    $transformedType = $collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals(
        "'foo' | 'bar'",
        $transformedType->transformed,
    );
})->skip(version_compare(PHP_VERSION, '8.1', '<'), 'Enums are a PHP 8.1+ feature.');

it('will collect annotated classes', function () {
    /** @typescript */
    $class = new class('a') extends Enum {
        const A = 'a';
    };

    $reflection = new ReflectionClass(
        $class
    );

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals(
        "'a' | 'yes' | 'no'",
        $transformedType->transformed,
    );
});

it('will collect annotated classes and use the given name', function () {
    /** @typescript EnumTransformed */
    $class = new class('a') extends Enum {
        const A = 'a';
    };

    $reflection = new ReflectionClass(
        $class
    );

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals('EnumTransformed', $transformedType->name);
    assertEquals(
        "'a' | 'yes' | 'no'",
        $transformedType->transformed,
    );
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

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals('DtoTransformed', $transformedType->name);
    assertEquals(
        '{'.PHP_EOL.'an_integer: number;'.PHP_EOL.'}',
        $transformedType->transformed,
    );
});

it('will throw an exception if a transformer is not found', function () {
    /** @typescript */
    $class = new class {
    };

    $reflection = new ReflectionClass(
        $class
    );

    $this->collector->getTransformedType($reflection);
})->throws(TransformerNotFound::class);

it('will collect classes with attributes', function () {
    $reflection = new ReflectionClass(WithTypeScriptAttribute::class);

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals('WithTypeScriptAttribute', $transformedType->name);
    assertEquals(
        "'a' | 'b'",
        $transformedType->transformed,
    );
});

it('will collect attribute overwritten transformers', function () {
    $reflection = new ReflectionClass(WithTypeScriptTransformerAttribute::class);

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals('WithTypeScriptTransformerAttribute', $transformedType->name);
    assertEquals(
        '{'.PHP_EOL.'an_int: number;'.PHP_EOL.'}',
        $transformedType->transformed,
    );
});

it('will collect classes with already transformed attributes', function () {
    $reflection = new ReflectionClass(WithAlreadyTransformedAttributeAttribute::class);

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertEquals(
        '{an_int:number;a_bool:boolean;}',
        $transformedType->transformed,
    );
});

it('can inline collected classes with annotations', function () {
    $reflection = new ReflectionClass(WithTypeScriptInlineAttribute::class);

    $transformedType = $this->collector->getTransformedType($reflection);

    assertNotNull($transformedType);
    assertTrue($transformedType->isInline);
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
     * @typescript DtoTransformed
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
