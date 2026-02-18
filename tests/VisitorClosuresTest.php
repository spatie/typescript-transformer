<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\TestSupport\InlineTransformedProvider;
use Spatie\TypeScriptTransformer\Tests\TestSupport\MemoryWriter;
use Spatie\TypeScriptTransformer\Tests\TestSupport\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('can run visitor closures when types are provided', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
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

    expect($writer->getOutput())->toEqual("export type someObject = string;\n");
});

it('can run visitor closures when types are connected', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->outputDirectory($this->temporaryDirectory->path())
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

    expect($writer->getOutput())->toEqual("export type someObject = string;\n");
});
