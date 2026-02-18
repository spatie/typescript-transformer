<?php

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularA;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularB;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\TypeScriptAttributedClass;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

it('can create a transformed collection', function () {
    $collection = new TransformedCollection([
        $initialTransformed = transformSingle(SimpleClass::class),
    ]);

    expect($collection)->toHaveCount(1);
});

it('can add transformed items to the collection', function () {
    $collection = new TransformedCollection();

    $collection->add(
        $initialTransformed = transformSingle(SimpleClass::class),
    );

    expect($collection)->toHaveCount(1);
});

it('can get a transformed item by reference', function () {
    $collection = new TransformedCollection([
        $classTransformed = transformSingle(SimpleClass::class),
        $manualTransformed = new Transformed(
            new TypeScriptString(),
            new CustomReference('vendor', 'package'),
            [],
        ),
    ]);

    expect($collection->has(new ClassStringReference(SimpleClass::class)))->toBeTrue();
    expect($collection->get(new ClassStringReference(SimpleClass::class)))->toBe($classTransformed);
    expect($collection->has(new CustomReference('vendor', 'package')))->toBeTrue();
    expect($collection->get(new CustomReference('vendor', 'package')))->toBe($manualTransformed);
});

it('can loop over items in the collection', function () {
    $collection = new TransformedCollection([
        $a = transformSingle(SimpleClass::class),
        $b = transformSingle(TypeScriptAttributedClass::class),
    ]);

    $found = [];

    foreach ($collection as $transformed) {
        $found[] = $transformed;
    }

    expect($found)->toBe([$a, $b]);
});

it('can loop over only changed items in the collection', function () {
    $collection = new TransformedCollection([
        $a = transformSingle(SimpleClass::class),
        $b = transformSingle(TypeScriptAttributedClass::class),
    ]);

    $a->changed = true;
    $b->changed = false;

    $found = [];

    foreach ($collection->onlyChanged() as $transformed) {
        $found[] = $transformed;
    }

    expect($found)->toBe([$a]);
});

it('all items added to the collection are marked as changed', function () {
    new TransformedCollection([
        $a = transformSingle(SimpleClass::class),
        $b = transformSingle(TypeScriptAttributedClass::class),
    ]);

    expect($a->changed)->toBeTrue();
    expect($b->changed)->toBeTrue();
});

it('can find transformed items by file path', function () {
    $collection = new TransformedCollection([
        $transformed = transformSingle(SimpleClass::class),
    ]);

    $path = __DIR__.'/../Fakes/TypesToProvide/SimpleClass.php';

    expect($collection->findTransformedByFile($path))->toBe($transformed);
});

it('can find transformed items by directory path', function () {
    $collection = new TransformedCollection([
        $a = transformSingle(SimpleClass::class),
        $b = transformSingle(TypeScriptAttributedClass::class),
        $c = transformSingle(CircularA::class),
    ]);

    $path = __DIR__.'/../Fakes/TypesToProvide';

    $found = [];

    foreach ($collection->findTransformedByDirectory($path) as $transformed) {
        $found[] = $transformed;
    }

    expect($found)->toBe([$a, $b]);
});

it('can check if any items in the collection have changed', function () {
    $collection = new TransformedCollection([
        $a = transformSingle(SimpleClass::class),
        $b = transformSingle(TypeScriptAttributedClass::class),
    ]);

    $a->changed = false;
    $b->changed = false;

    expect($collection->hasChanges())->toBeFalse();

    $a->changed = true;

    expect($collection->hasChanges())->toBeTrue();
});

it('can remove a transformed item by reference', function () {
    $collection = new TransformedCollection([
        transformSingle(SimpleClass::class),
    ]);

    $collection->remove(new ClassStringReference(SimpleClass::class));

    expect($collection->has(new ClassStringReference(SimpleClass::class)))->toBeFalse();
});

it('can remove a transformed item by reference and update references', function () {
    [$collection] = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories(__DIR__.'/../Fakes/Circular')
    )->resolveState();

    foreach ($collection as $transformed) {
        $transformed->changed = false;
    }

    $collection->remove($referenceA = new ClassStringReference(CircularA::class));

    expect($collection)->toHaveCount(1);

    $transformedB = $collection->get($referenceB = new ClassStringReference(CircularB::class));

    expect($transformedB->changed)->toBeTrue();
    expect($transformedB->missingReferences)->toHaveCount(1);
    expect($transformedB->missingReferences)->toHaveKey($referenceA->getKey());
    expect($transformedB->missingReferences[$referenceA->getKey()])
        ->toBeArray()
        ->each()
        ->toBeInstanceOf(TypeScriptReference::class);
    expect($transformedB->referencedBy)->not->toContain($referenceA->getKey());
});
