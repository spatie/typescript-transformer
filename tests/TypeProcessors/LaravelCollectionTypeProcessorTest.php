<?php

use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\LaravelCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeReflectors\TypeReflector;

function processType(object $class, string $property): Type
{
    $reflection = new ReflectionProperty($class, $property);

    return test()->processor->process(
        TypeReflector::new($reflection)->reflectFromDocblock(),
        $reflection,
        new TypeReferencesCollection()
    );
}

beforeEach(function () {
    $this->processor = new LaravelCollectionTypeProcessor();

    $this->typeResolver = new TypeResolver();
});

it('works with single types', function () {
    $class = new class {
        /** @var int[] */
        public Collection $propertyA;

        /** @var ?int[] */
        public Collection $propertyB;

        /** @var int[]|null */
        public ?Collection $propertyC;

        /** @var array */
        public Collection $propertyD;

        /** @var ?array */
        public ?Collection $propertyE;

        /** @var array|null */
        public ?Collection $propertyF;

        /** @var \Illuminate\Support\Collection */
        public Collection $propertyG;

        /** @var \Illuminate\Support\Collection|int[] */
        public Collection $propertyH;

        /** @var \Illuminate\Support\Collection|int[]|null */
        public ?Collection $propertyI;
    };

    expect((string) processType($class, 'propertyA'))->toEqual('int[]');
    expect((string) processType($class, 'propertyB'))->toEqual('?int[]');
    expect((string) processType($class, 'propertyC'))->toEqual('int[]|null');
    expect((string) processType($class, 'propertyD'))->toEqual('array');
    expect((string) processType($class, 'propertyE'))->toEqual('?array');
    expect((string) processType($class, 'propertyF'))->toEqual('array|null');
    expect((string) processType($class, 'propertyG'))->toEqual('array');
    expect((string) processType($class, 'propertyH'))->toEqual('int[]');
    expect((string) processType($class, 'propertyI'))->toEqual('int[]|null');
});

it('works with union types', function () {
    $class = new class {
        /** @var \Illuminate\Support\Collection|int[] */
        public Collection|array $property;
    };

    expect((string) processType($class, 'property'))->toEqual('int[]');
});
