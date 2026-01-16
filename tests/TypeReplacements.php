<?php

use Carbon\Carbon;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\Tests\Support\InlineTransformedProvider;
use Spatie\TypeScriptTransformer\Tests\Support\MemoryWriter;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

it('can replace types', function (
    mixed $replacement,
    TypeScriptNode $expected,
) {
    $config = TypeScriptTransformerConfigFactory::create()
        ->provider(new InlineTransformedProvider(TransformedFactory::alias(
            'date',
            new TypeScriptObject([
                new TypeScriptProperty('datetime', new TypeReference(new ClassStringReference(DateTime::class))),
            ])
        )))
        ->writer($writer = new MemoryWriter())
        ->replaceType(DateTime::class, $replacement)
        ->get();

    TypeScriptTransformer::create($config)->execute();

    expect($writer->getTransformedNodeByName('date'))->toEqual(new TypeScriptAlias(
        new TypeScriptIdentifier('date'),
        $expected
    ));
})->with(function () {
    yield 'with a user defined PHP type' => [
        'replacement' => 'string',
        'expected' => new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptString()),
        ]),
    ];

    yield 'with a user defined complex PHP type' => [
        'replacement' => 'array{day: int, month: int, year: int}',
        'expected' => new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptObject([
                new TypeScriptProperty('day', new TypeScriptNumber()),
                new TypeScriptProperty('month', new TypeScriptNumber()),
                new TypeScriptProperty('year', new TypeScriptNumber()),
            ])),
        ]),
    ];

    yield 'with a user defined type' => [
        'replacement' => 'JsDate',
        'expected' => new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptRaw('JsDate')),
        ]),
    ];

    yield 'with a typescript node' => [
        'replacement' => new TypeScriptObject([
            new TypeScriptProperty('date', new TypeScriptString()),
        ]),
        'expected' => new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptObject([
                new TypeScriptProperty('date', new TypeScriptString()),
            ])),
        ]),
    ];

    yield 'using a closure' => [
        'replacement' => fn (TypeScriptNode $node) => new TypeScriptObject([
            new TypeScriptProperty('date', new TypeScriptString()),
        ]),
        'expected' => new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptObject([
                new TypeScriptProperty('date', new TypeScriptString()),
            ])),
        ]),
    ];
});

it('will replace inherited types', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->provider(new InlineTransformedProvider(TransformedFactory::alias(
            'date',
            new TypeScriptObject([
                new TypeScriptProperty('datetime', new TypeReference(new ClassStringReference(Carbon::class))),
            ])
        )))
        ->writer($writer = new MemoryWriter())
        ->replaceType(DateTime::class, 'string')
        ->get();

    TypeScriptTransformer::create($config)->execute();

    expect($writer->getTransformedNodeByName('date'))->toEqual(new TypeScriptAlias(
        new TypeScriptIdentifier('date'),
        new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptString()),
        ])
    ));
});

it('will replace implemented types', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->provider(new InlineTransformedProvider(TransformedFactory::alias(
            'date',
            new TypeScriptObject([
                new TypeScriptProperty('datetime', new TypeReference(new ClassStringReference(Carbon::class))),
            ])
        )))
        ->writer($writer = new MemoryWriter())
        ->replaceType(DateTimeInterface::class, 'string')
        ->get();

    TypeScriptTransformer::create($config)->execute();

    expect($writer->getTransformedNodeByName('date'))->toEqual(new TypeScriptAlias(
        new TypeScriptIdentifier('date'),
        new TypeScriptObject([
            new TypeScriptProperty('datetime', new TypeScriptString()),
        ])
    ));
});
