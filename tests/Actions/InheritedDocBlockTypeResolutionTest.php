<?php

use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\PhpNodes\PhpPropertyNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\Children\ChildWithInheritedDocBlockType;
use Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\ParentWithDocBlockType;
use Spatie\TypeScriptTransformer\Tests\Fakes\InheritedProperties\SomeGenericClass;
use Spatie\TypeScriptTransformer\TypeResolvers\DocTypeResolver;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;

it('resolves inherited docblock types using the declaring class context, not the child class', function () {
    $docTypeResolver = new DocTypeResolver();
    $transpiler = new TranspilePhpStanTypeToTypeScriptNodeAction();

    // When resolving the parent class directly, SomeGenericClass<int, string>
    // resolves correctly because the parent and SomeGenericClass share a namespace
    $parentNode = new PhpClassNode(new ReflectionClass(ParentWithDocBlockType::class));
    $propertyNode = new PhpPropertyNode(new ReflectionProperty(ParentWithDocBlockType::class, 'items'));
    $annotation = $docTypeResolver->property($propertyNode);

    $parentResult = $transpiler->execute($annotation->type, $parentNode);

    expect($parentResult)->toBeInstanceOf(TypeScriptUnion::class);
    expect($parentResult->types[0])->toBeInstanceOf(TypeScriptArray::class);
    expect($parentResult->types[1])->toBeInstanceOf(TypeScriptGeneric::class);
    expect($parentResult->types[1]->type)->toBeInstanceOf(TypeScriptReference::class);
    expect($parentResult->types[1]->type->reference)->toBeInstanceOf(ClassStringReference::class);
    expect($parentResult->types[1]->type->reference->classString)->toBe(SomeGenericClass::class);

    // When resolving the same property through the child class context,
    // the annotation is inherited from the parent's @var docblock.
    // The child is in a different namespace and does NOT import SomeGenericClass.
    // The transpiler should resolve SomeGenericClass using the declaring (parent)
    // class's namespace, not the child's.
    $childNode = new PhpClassNode(new ReflectionClass(ChildWithInheritedDocBlockType::class));
    $childPropertyNode = new PhpPropertyNode(new ReflectionProperty(ChildWithInheritedDocBlockType::class, 'items'));
    $childAnnotation = $docTypeResolver->property($childPropertyNode);

    expect($childAnnotation)->not->toBeNull();

    $childResult = $transpiler->execute($childAnnotation->type, $childNode);

    expect($childResult)->toBeInstanceOf(TypeScriptUnion::class);
    expect($childResult->types[0])->toBeInstanceOf(TypeScriptArray::class);
    // BUG: This currently resolves to TypeScriptUnknown because the transpiler
    // uses FindClassNameFqcnAction with the child class's file/namespace context,
    // which doesn't know about SomeGenericClass. It should use the declaring
    // (parent) class's context instead.
    expect($childResult->types[1])->toBeInstanceOf(TypeScriptGeneric::class);
    expect($childResult->types[1]->type)->toBeInstanceOf(TypeScriptReference::class);
    expect($childResult->types[1]->type->reference)->toBeInstanceOf(ClassStringReference::class);
    expect($childResult->types[1]->type->reference->classString)->toBe(SomeGenericClass::class);
});
