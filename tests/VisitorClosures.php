<?php

use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Tests\Support\InlineTransformedProvider;
use Spatie\TypeScriptTransformer\Tests\Support\MemoryWriter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

it('can run visitor closures when types are provided', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->provider(new InlineTransformedProvider(TransformedFactory::alias(
            'someObject',
            new TypeScriptObject([
                new TypeScriptProperty('name', new TypeScriptReference(new ClassStringReference(DateTime::class))),
            ])
        )))
        ->writer($writer = new MemoryWriter())
        ->providedVisitorHook(function (TypeScriptObject $reference) {
            return VisitorOperation::replace(new TypeScriptString());
        }, [TypeScriptObject::class])
        ->get();

    TypeScriptTransformer::create($config)->execute();

    expect($writer->getOutput())->toEqual('type someObject = string;');
});

it('can run visitor closures when types are connected', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->provider(new InlineTransformedProvider(TransformedFactory::alias(
            'someObject',
            new TypeScriptObject([
                new TypeScriptProperty('name', new TypeScriptReference(new ClassStringReference(DateTime::class))),
            ])
        )))
        ->writer($writer = new MemoryWriter())
        ->connectedVisitorHook(function (TypeScriptObject $reference) {
            return VisitorOperation::replace(new TypeScriptString());
        }, [TypeScriptObject::class])
        ->get();

    TypeScriptTransformer::create($config)->execute();

    expect($writer->getOutput())->toEqual('type someObject = string;');
});
