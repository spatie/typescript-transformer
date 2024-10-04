<?php

use Spatie\TypeScriptTransformer\Actions\ConnectReferencesAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\CustomReference;
use Spatie\TypeScriptTransformer\Support\Console\WrappedNullConsole;
use Spatie\TypeScriptTransformer\Support\TypeScriptTransformerLog;
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
        new TypeScriptTransformerLog(new WrappedNullConsole())
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
