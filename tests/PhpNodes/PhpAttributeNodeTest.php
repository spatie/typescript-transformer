<?php

use Spatie\TypeScriptTransformer\PhpNodes\PhpAttributeNode;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\ComplexAttribute;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleAttribute;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\VariadicAttribute;

it('can check and get named arguments from a simple attribute', function () {
    #[SimpleAttribute(69)]
    class TestSimpleAttributeNamedArgumentFetch
    {
    }

    $attributeNode = createAttributeNode(TestSimpleAttributeNamedArgumentFetch::class, SimpleAttribute::class);

    expect($attributeNode->getRawArguments())->toBe([69]);

    expect($attributeNode->hasArgument('argument'))->toBeTrue();
    expect($attributeNode->getArgument('argument'))->toBe(69);

    expect($attributeNode->hasArgument('default'))->toBeTrue();
    expect($attributeNode->getArgument('default'))->toBe(42);
});

it('can check and get named arguments from a simple attribute with default set', function () {
    #[SimpleAttribute(69, 314)]
    class TestSimpleAttributeNamedArgumentFetchWithDefaultSet
    {
    }

    $attributeNode = createAttributeNode(TestSimpleAttributeNamedArgumentFetchWithDefaultSet::class, SimpleAttribute::class);

    expect($attributeNode->getRawArguments())->toBe([69, 314]);

    expect($attributeNode->hasArgument('argument'))->toBeTrue();
    expect($attributeNode->getArgument('argument'))->toBe(69);

    expect($attributeNode->hasArgument('default'))->toBeTrue();
    expect($attributeNode->getArgument('default'))->toBe(314);
});

it('can check and get named arguments from a simple attribute with specific naming', function () {
    #[SimpleAttribute(argument: 69)]
    class TestSimpleAttributeNamedArgumentFetchWithSpecificNaming
    {
    }

    $attributeNode = createAttributeNode(TestSimpleAttributeNamedArgumentFetchWithSpecificNaming::class, SimpleAttribute::class);

    expect($attributeNode->getRawArguments())->toBe(['argument' => 69]);

    expect($attributeNode->hasArgument('argument'))->toBeTrue();
    expect($attributeNode->getArgument('argument'))->toBe(69);

    expect($attributeNode->hasArgument('default'))->toBeTrue();
    expect($attributeNode->getArgument('default'))->toBe(42);
});

it('can check and get named arguments from a simple attribute with named and unnamed attributes', function () {
    #[SimpleAttribute(69, default: 314)]
    class TestSimpleAttributeNamedAndUnnamedArgumentFetchWithSpecificNaming
    {
    }

    $attributeNode = createAttributeNode(TestSimpleAttributeNamedAndUnnamedArgumentFetchWithSpecificNaming::class, SimpleAttribute::class);

    expect($attributeNode->getRawArguments())->toBe([0 => 69, 'default' => 314]);

    expect($attributeNode->hasArgument('argument'))->toBeTrue();
    expect($attributeNode->getArgument('argument'))->toBe(69);

    expect($attributeNode->hasArgument('default'))->toBeTrue();
    expect($attributeNode->getArgument('default'))->toBe(314);
});

it('can mix and match named parameters in reversed order', function () {
    #[ComplexAttribute(argumentD: 'DD', argumentB: 2, argumentA: 1)]
    class TestSimpleAttributeNamedAndUnnamedArgumentFetchWithReversedOrder
    {
    }

    $attributeNode = createAttributeNode(TestSimpleAttributeNamedAndUnnamedArgumentFetchWithReversedOrder::class, ComplexAttribute::class);

    expect($attributeNode->getRawArguments())->toBe(['argumentD' => 'DD', 'argumentB' => 2, 'argumentA' => 1]);

    expect($attributeNode->hasArgument('argumentA'))->toBeTrue();
    expect($attributeNode->getArgument('argumentA'))->toBe(1);

    expect($attributeNode->hasArgument('argumentB'))->toBeTrue();
    expect($attributeNode->getArgument('argumentB'))->toBe(2);

    expect($attributeNode->hasArgument('argumentC'))->toBeTrue();
    expect($attributeNode->getArgument('argumentC'))->toBe('C');

    expect($attributeNode->hasArgument('argumentD'))->toBeTrue();
    expect($attributeNode->getArgument('argumentD'))->toBe('DD');
});

it('can use variadic parameters', function () {
    #[VariadicAttribute(1, 2, 3, 4)]
    class TestVariadicAttribute
    {
    }

    $attributeNode = createAttributeNode(TestVariadicAttribute::class, VariadicAttribute::class);

    expect($attributeNode->getRawArguments())->toBe([1, 2, 3, 4]);

    expect($attributeNode->hasArgument('argument'))->toBeTrue();
    expect($attributeNode->getArgument('argument'))->toBe(1);

    expect($attributeNode->hasArgument('variadic'))->toBeTrue();
    expect($attributeNode->getArgument('variadic'))->toBe([2, 3, 4]);
});
function createAttributeNode(string $className, string $attributeClass): PhpAttributeNode
{
    $reflection = new ReflectionClass($className);
    $attribute = $reflection->getAttributes($attributeClass)[0];

    return new PhpAttributeNode($attribute);
}
