<?php

use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\Location;
use Spatie\TypeScriptTransformer\Tests\TestSupport\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can split per location into a tree', function () {
    $transformedCollection = new TransformedCollection([
        $level11 = TransformedFactory::alias('Level1Type', new TypeScriptString(), location: ['level1'])->build(),
        $root1 = TransformedFactory::alias('RootType', new TypeScriptString())->build(),
        $level2 = TransformedFactory::alias('Level2Type', new TypeScriptString(), location: ['level1', 'level2'])->build(),
        $level12 = TransformedFactory::alias('Level1Type2', new TypeScriptString(), location: ['level1'])->build(),
        $root2 = TransformedFactory::alias('RootType2', new TypeScriptString())->build(),
    ]);

    $root = (new SplitTransformedPerLocationAction())->execute(
        $transformedCollection->all()
    );

    expect($root)
        ->toBeInstanceOf(Location::class)
        ->name->toBe('')
        ->path->toBeEmpty()
        ->transformed->toEqual([$root1, $root2]);

    expect($root->children)->toHaveCount(1);

    $level1 = $root->children[0];

    expect($level1)
        ->toBeInstanceOf(Location::class)
        ->name->toBe('level1')
        ->path->toBe(['level1'])
        ->transformed->toEqual([$level11, $level12]);

    expect($level1->children)->toHaveCount(1);

    $level2Node = $level1->children[0];

    expect($level2Node)
        ->toBeInstanceOf(Location::class)
        ->name->toBe('level2')
        ->path->toBe(['level1', 'level2'])
        ->transformed->toEqual([$level2]);

    expect($level2Node->children)->toBeEmpty();
});
