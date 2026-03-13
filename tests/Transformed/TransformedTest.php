<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\Loggers\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptForwardingNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;

it('can get the name of a transformed when having a named node', function () {
    $transformed = transformSingle(
        \Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum::class,
        new EnumTransformer(useUnionEnums: false)
    );

    expect($transformed->getNode())->toBeInstanceOf(TypeScriptNamedNode::class);
    expect($transformed->getName())->toBe('StringBackedEnum');
});

it('can get the name of a transformed when having a forwarding named node', function () {
    $transformed = transformSingle(
        \Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum::class,
        new EnumTransformer(useUnionEnums: true)
    );

    expect($transformed->getNode())->toBeInstanceOf(TypeScriptForwardingNamedNode::class);
    expect($transformed->getName())->toBe('StringBackedEnum');
});

it('can manually set the name of a transformed', function () {
    $transformed = transformSingle(
        \Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum::class,
        new EnumTransformer(useUnionEnums: false)
    );

    $transformed->nameAs('MyEnum');

    expect($transformed->getName())->toBe('MyEnum');
});

it('can add a missing reference', function () {
    $missing = transformSingle(SimpleClass::class);

    $transformed = new Transformed(
        new TypeScriptObject([
            new TypeScriptProperty('first_name', $typeReferenceA = new TypeScriptReference($missing->getReference())),
            new TypeScriptProperty('last_name', $typeReferenceB = new TypeScriptReference($missing->getReference())),
        ]),
        new CustomReference('vendor', 'package'),
        [],
    );

    $transformed->addMissingReference(
        $missing->getReference(),
        $typeReferenceA
    );

    expect($transformed->getMissingReferences())->toBe([
        $missing->getReference()->getKey() => [$typeReferenceA],
    ]);

    $transformed->addMissingReference(
        $missing->getReference(),
        $typeReferenceB
    );

    expect($transformed->getMissingReferences())->toBe([
        $missing->getReference()->getKey() => [$typeReferenceA, $typeReferenceB],
    ]);
});

it('can mark a missing reference as found', function () {
    $missing = transformSingle(SimpleClass::class);

    $transformed = new Transformed(
        new TypeScriptObject([
            new TypeScriptProperty('first_name', $typeReferenceA = new TypeScriptReference($missing->getReference())),
            new TypeScriptProperty('last_name', $typeReferenceB = new TypeScriptReference($missing->getReference())),
        ]),
        new CustomReference('vendor', 'package'),
        [],
    );

    $transformed->addMissingReference(
        $missing->getReference(),
        $typeReferenceA
    );

    $transformed->addMissingReference(
        $missing->getReference(),
        $typeReferenceB
    );

    // Use write() to reset changed to false
    $missing->write(new \Spatie\TypeScriptTransformer\Data\WritingContext([]));
    $transformed->write(new \Spatie\TypeScriptTransformer\Data\WritingContext([]));

    $transformed->markMissingReferenceFound($missing);

    expect($missing->isChanged())->toBeFalse();
    expect($transformed->isChanged())->toBeTrue();

    expect($transformed->getMissingReferences())->toBeEmpty();
    expect($transformed->getReferences()[$missing->getReference()->getKey()])->toBe([
        $typeReferenceA,
        $typeReferenceB,
    ]);

    expect($typeReferenceA->referenced)->toBe($missing);
    expect($typeReferenceB->referenced)->toBe($missing);

    expect($missing->getReferencedBy())->toBe([$transformed->getReference()->getKey()]);
});

it('can mark a reference as missing', function () {
    $found = transformSingle(SimpleClass::class);

    $transformed = new Transformed(
        new TypeScriptObject([
            new TypeScriptProperty('first_name', $typeReferenceA = new TypeScriptReference($found->getReference())),
            new TypeScriptProperty('last_name', $typeReferenceB = new TypeScriptReference($found->getReference())),
        ]),
        new CustomReference('vendor', 'package'),
        [],
    );

    $connector = new ConnectReferencesAction(
        new NullLogger(),
    );

    $connector->execute(new TransformedCollection([$found, $transformed]));

    // Use write() to reset changed to false
    $transformed->write(new \Spatie\TypeScriptTransformer\Data\WritingContext([]));
    $found->write(new \Spatie\TypeScriptTransformer\Data\WritingContext([]));

    expect($transformed->getMissingReferences())->toBeEmpty();

    $transformed->markReferenceMissing($found);

    expect($transformed->isChanged())->toBeTrue();

    expect($transformed->getReferences())->toBeEmpty();
    expect($transformed->getMissingReferences())->toBe([
        $found->getReference()->getKey() => [$typeReferenceA, $typeReferenceB],
    ]);

    expect($typeReferenceA->referenced)->toBeNull();
    expect($typeReferenceB->referenced)->toBeNull();
});

it('equals returns true for identical transformed objects', function () {
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);

    expect($transformed1->equals($transformed2))->toBeTrue();
});

it('equals returns false for transformed objects with different names', function () {
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);
    $transformed2->nameAs('DifferentName');

    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals compares referencedBy arrays correctly', function () {
    // Same order
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);
    $transformed1->referencedBy('A');
    $transformed1->referencedBy('B');
    $transformed2->referencedBy('A');
    $transformed2->referencedBy('B');
    expect($transformed1->equals($transformed2))->toBeTrue();

    // Different order, should still be equal
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);
    $transformed1->referencedBy('A');
    $transformed1->referencedBy('B');
    $transformed2->referencedBy('B');
    $transformed2->referencedBy('A');
    expect($transformed1->equals($transformed2))->toBeTrue();

    // Different count
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);
    $transformed1->referencedBy('A');
    $transformed1->referencedBy('B');
    $transformed2->referencedBy('A');
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different values
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);
    $transformed1->referencedBy('A');
    $transformed1->referencedBy('B');
    $transformed2->referencedBy('A');
    $transformed2->referencedBy('C');
    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals compares references arrays correctly', function () {
    $ref1 = new CustomReference('test1', 'class1');
    $ref2 = new CustomReference('test2', 'class2');

    // Both empty references
    $transformed1 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor1', 'package1'),
        [],
    );
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    expect($transformed1->equals($transformed2))->toBeTrue();

    // Add same references
    $typeRef1A = new TypeScriptReference($ref1);
    $typeRef1B = new TypeScriptReference($ref2);
    $typeRef2A = new TypeScriptReference($ref1);
    $typeRef2B = new TypeScriptReference($ref2);

    $transformed1 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor1', 'package1'),
        [],
    );
    $transformed1->references('key1', $typeRef1A);
    $transformed1->references('key2', $typeRef1B);

    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->references('key1', $typeRef2A);
    $transformed2->references('key2', $typeRef2B);

    expect($transformed1->equals($transformed2))->toBeTrue();

    // Different keys
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->references('key1', $typeRef2A);
    $transformed2->references('key3', $typeRef2B);
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different reference counts for same key
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->references('key1', $typeRef2A);
    $transformed2->references('key1', $typeRef2B);
    $transformed2->references('key2', $typeRef2B);
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different reference types for same key
    $typeRef3 = new TypeScriptReference(new CustomReference('different', 'ref'));
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->references('key1', $typeRef3);
    $transformed2->references('key2', $typeRef2B);
    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals compares missingReferences arrays correctly', function () {
    $ref1 = new CustomReference('missing1', 'class1');
    $ref2 = new CustomReference('missing2', 'class2');

    // Both empty missingReferences
    $transformed1 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor1', 'package1'),
        [],
    );
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    expect($transformed1->equals($transformed2))->toBeTrue();

    // Add same missing references
    $typeRef1A = new TypeScriptReference($ref1);
    $typeRef1B = new TypeScriptReference($ref2);
    $typeRef2A = new TypeScriptReference($ref1);
    $typeRef2B = new TypeScriptReference($ref2);

    $transformed1 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor1', 'package1'),
        [],
    );
    $transformed1->addMissingReference('missing1', $typeRef1A);
    $transformed1->addMissingReference('missing2', $typeRef1B);

    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->addMissingReference('missing1', $typeRef2A);
    $transformed2->addMissingReference('missing2', $typeRef2B);

    expect($transformed1->equals($transformed2))->toBeTrue();

    // Different missing reference keys
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->addMissingReference('missing1', $typeRef2A);
    $transformed2->addMissingReference('missing3', $typeRef2B);
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different missing reference counts for same key
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->addMissingReference('missing1', $typeRef2A);
    $transformed2->addMissingReference('missing1', $typeRef2B);
    $transformed2->addMissingReference('missing2', $typeRef2B);
    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals handles complex scenario with all arrays populated', function () {
    $ref1 = new CustomReference('complex1', 'class1');
    $ref2 = new CustomReference('complex2', 'class2');

    $typeRef1A = new TypeScriptReference($ref1);
    $typeRef1B = new TypeScriptReference($ref2);
    $typeRef2A = new TypeScriptReference($ref1);
    $typeRef2B = new TypeScriptReference($ref2);

    // Set up identical complex state
    $transformed1 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor1', 'package1'),
        [],
    );
    $transformed1->referencedBy('ref1');
    $transformed1->referencedBy('ref2');
    $transformed1->references('key1', $typeRef1A);
    $transformed1->addMissingReference('missing1', $typeRef1B);

    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->referencedBy('ref2');
    $transformed2->referencedBy('ref1'); // Different order, should still be equal
    $transformed2->references('key1', $typeRef2A);
    $transformed2->addMissingReference('missing1', $typeRef2B);

    expect($transformed1->equals($transformed2))->toBeTrue();

    // Change one reference and verify it fails
    $typeRef3 = new TypeScriptReference(new CustomReference('different', 'ref'));
    $transformed2 = new Transformed(
        new TypeScriptObject([]),
        new CustomReference('vendor2', 'package2'),
        [],
    );
    $transformed2->referencedBy('ref2');
    $transformed2->referencedBy('ref1');
    $transformed2->references('key1', $typeRef3);
    $transformed2->addMissingReference('missing1', $typeRef2B);

    expect($transformed1->equals($transformed2))->toBeFalse();
});
