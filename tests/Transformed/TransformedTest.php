<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\Console\NullLogger;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptForwardingNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNamedNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;

it('can get the name of a transformed when having a named node', function () {
    $transformed = transformSingle(
        \Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum::class,
        new EnumTransformer(useUnionEnums: false)
    );

    expect($transformed->typeScriptNode)->toBeInstanceOf(TypeScriptNamedNode::class);
    expect($transformed->getName())->toBe('StringBackedEnum');
});

it('can get the name of a transformed when having a forwarding named node', function () {
    $transformed = transformSingle(
        \Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum::class,
        new EnumTransformer(useUnionEnums: true)
    );

    expect($transformed->typeScriptNode)->toBeInstanceOf(TypeScriptForwardingNamedNode::class);
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
            new TypeScriptProperty('first_name', $typeReferenceA = new TypeReference($missing->reference)),
            new TypeScriptProperty('last_name', $typeReferenceB = new TypeReference($missing->reference)),
        ]),
        new CustomReference('vendor', 'package'),
        [],
    );

    $transformed->addMissingReference(
        $missing->reference,
        $typeReferenceA
    );

    expect($transformed->missingReferences)->toBe([
        $missing->reference->getKey() => [$typeReferenceA],
    ]);

    $transformed->addMissingReference(
        $missing->reference,
        $typeReferenceB
    );

    expect($transformed->missingReferences)->toBe([
        $missing->reference->getKey() => [$typeReferenceA, $typeReferenceB],
    ]);
});

it('can mark a missing reference as found', function () {
    $missing = transformSingle(SimpleClass::class);

    $transformed = new Transformed(
        new TypeScriptObject([
            new TypeScriptProperty('first_name', $typeReferenceA = new TypeReference($missing->reference)),
            new TypeScriptProperty('last_name', $typeReferenceB = new TypeReference($missing->reference)),
        ]),
        new CustomReference('vendor', 'package'),
        [],
    );

    $transformed->addMissingReference(
        $missing->reference,
        $typeReferenceA
    );

    $transformed->addMissingReference(
        $missing->reference,
        $typeReferenceB
    );

    $missing->changed = false;
    $transformed->changed = false;

    $transformed->markMissingReferenceFound($missing);

    expect($missing->changed)->toBeFalse();
    expect($transformed->changed)->toBeTrue();

    expect($transformed->missingReferences)->toBeEmpty();
    expect($transformed->references[$missing->reference->getKey()])->toBe([
        $typeReferenceA,
        $typeReferenceB,
    ]);

    expect($typeReferenceA->referenced)->toBe($missing);
    expect($typeReferenceB->referenced)->toBe($missing);

    expect($missing->referencedBy)->toBe([$transformed->reference->getKey()]);
});

it('can mark a reference as missing', function () {
    $found = transformSingle(SimpleClass::class);

    $transformed = new Transformed(
        new TypeScriptObject([
            new TypeScriptProperty('first_name', $typeReferenceA = new TypeReference($found->reference)),
            new TypeScriptProperty('last_name', $typeReferenceB = new TypeReference($found->reference)),
        ]),
        new CustomReference('vendor', 'package'),
        [],
    );

    $connector = new ConnectReferencesAction(
        new NullLogger(),
    );

    $connector->execute(new TransformedCollection([$found, $transformed]));

    $transformed->changed = false;
    $found->changed = false;

    expect($transformed->missingReferences)->toBeEmpty();

    $transformed->markReferenceMissing($found);

    expect($transformed->changed)->toBeTrue();

    expect($transformed->references)->toBeEmpty();
    expect($transformed->missingReferences)->toBe([
        $found->reference->getKey() => [$typeReferenceA, $typeReferenceB],
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
    $transformed1 = transformSingle(SimpleClass::class);
    $transformed2 = transformSingle(SimpleClass::class);

    $transformed1->referencedBy = ['A', 'B'];
    $transformed2->referencedBy = ['A', 'B'];
    expect($transformed1->equals($transformed2))->toBeTrue();

    $transformed1->referencedBy = ['A', 'B'];
    $transformed2->referencedBy = ['B', 'A'];
    expect($transformed1->equals($transformed2))->toBeTrue();

    $transformed1->referencedBy = ['A', 'B'];
    $transformed2->referencedBy = ['A'];
    expect($transformed1->equals($transformed2))->toBeFalse();

    $transformed1->referencedBy = ['A', 'B'];
    $transformed2->referencedBy = ['A', 'C'];
    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals compares references arrays correctly', function () {
    $ref1 = new CustomReference('test1', 'class1');
    $ref2 = new CustomReference('test2', 'class2');

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

    // Both empty references
    expect($transformed1->equals($transformed2))->toBeTrue();

    // Add same references
    $typeRef1A = new TypeReference($ref1);
    $typeRef1B = new TypeReference($ref2);
    $typeRef2A = new TypeReference($ref1);
    $typeRef2B = new TypeReference($ref2);

    $transformed1->references = [
        'key1' => [$typeRef1A],
        'key2' => [$typeRef1B],
    ];
    $transformed2->references = [
        'key1' => [$typeRef2A],
        'key2' => [$typeRef2B],
    ];

    expect($transformed1->equals($transformed2))->toBeTrue();

    // Different keys
    $transformed2->references = [
        'key1' => [$typeRef2A],
        'key3' => [$typeRef2B],
    ];
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different reference counts for same key
    $transformed2->references = [
        'key1' => [$typeRef2A, $typeRef2B],
        'key2' => [$typeRef2B],
    ];
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different reference types for same key
    $typeRef3 = new TypeReference(new CustomReference('different', 'ref'));
    $transformed2->references = [
        'key1' => [$typeRef3],
        'key2' => [$typeRef2B],
    ];
    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals compares missingReferences arrays correctly', function () {
    $ref1 = new CustomReference('missing1', 'class1');
    $ref2 = new CustomReference('missing2', 'class2');

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

    // Both empty missingReferences
    expect($transformed1->equals($transformed2))->toBeTrue();

    // Add same missing references
    $typeRef1A = new TypeReference($ref1);
    $typeRef1B = new TypeReference($ref2);
    $typeRef2A = new TypeReference($ref1);
    $typeRef2B = new TypeReference($ref2);

    $transformed1->missingReferences = [
        'missing1' => [$typeRef1A],
        'missing2' => [$typeRef1B],
    ];
    $transformed2->missingReferences = [
        'missing1' => [$typeRef2A],
        'missing2' => [$typeRef2B],
    ];

    expect($transformed1->equals($transformed2))->toBeTrue();

    // Different missing reference keys
    $transformed2->missingReferences = [
        'missing1' => [$typeRef2A],
        'missing3' => [$typeRef2B],
    ];
    expect($transformed1->equals($transformed2))->toBeFalse();

    // Different missing reference counts for same key
    $transformed2->missingReferences = [
        'missing1' => [$typeRef2A, $typeRef2B],
        'missing2' => [$typeRef2B],
    ];
    expect($transformed1->equals($transformed2))->toBeFalse();
});

it('equals handles complex scenario with all arrays populated', function () {
    $ref1 = new CustomReference('complex1', 'class1');
    $ref2 = new CustomReference('complex2', 'class2');

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

    // Set up identical complex state
    $transformed1->referencedBy = ['ref1', 'ref2'];
    $transformed2->referencedBy = ['ref2', 'ref1']; // Different order, should still be equal

    $typeRef1A = new TypeReference($ref1);
    $typeRef1B = new TypeReference($ref2);
    $typeRef2A = new TypeReference($ref1);
    $typeRef2B = new TypeReference($ref2);

    $transformed1->references = ['key1' => [$typeRef1A]];
    $transformed1->missingReferences = ['missing1' => [$typeRef1B]];

    $transformed2->references = ['key1' => [$typeRef2A]];
    $transformed2->missingReferences = ['missing1' => [$typeRef2B]];

    expect($transformed1->equals($transformed2))->toBeTrue();

    // Change one reference and verify it fails
    $typeRef3 = new TypeReference(new CustomReference('different', 'ref'));
    $transformed2->references = ['key1' => [$typeRef3]];

    expect($transformed1->equals($transformed2))->toBeFalse();
});
