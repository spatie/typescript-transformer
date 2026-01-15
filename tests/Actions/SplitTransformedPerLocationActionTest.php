<?php

use Spatie\TypeScriptTransformer\Actions\SplitTransformedPerLocationAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\Location;
use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can split per location', function () {
    $transformedCollection = new TransformedCollection([
        $level11 = TransformedFactory::alias('Level1Type', new TypeScriptString(), location: ['level1'])->build(),
        $root1 = TransformedFactory::alias('RootType', new TypeScriptString())->build(),
        $level2 = TransformedFactory::alias('Level2Type', new TypeScriptString(), location: ['level1', 'level2'])->build(),
        $level12 = TransformedFactory::alias('Level1Type2', new TypeScriptString(), location: ['level1'])->build(),
        $root2 = TransformedFactory::alias('RootType2', new TypeScriptString())->build(),
    ]);

    $split = (new SplitTransformedPerLocationAction())->execute(
        $transformedCollection->all()
    );

    expect($split)->toHaveCount(3);

    expect($split[0])
        ->toBeInstanceOf(Location::class)
        ->segments->toBeEmpty()
        ->transformed->toEqual([$root1, $root2]);

    expect($split[1])
        ->toBeInstanceOf(Location::class)
        ->segments->toBe(['level1'])
        ->transformed->toEqual([$level11, $level12]);

    expect($split[2])
        ->toBeInstanceOf(Location::class)
        ->segments->toBe(['level1', 'level2'])
        ->transformed->toEqual([$level2]);
});
