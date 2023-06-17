<?php

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertEquals;
use function Spatie\Snapshots\assertMatchesSnapshot;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Types\StructType;

beforeEach(function () {
    $this->missingSymbols = new MissingSymbolsCollection();

    $this->typeResolver = new TypeResolver();

    $this->action = new TranspileTypeToTypeScriptAction(
        $this->missingSymbols,
        'fake_class'
    );
});

it('can resolve types', function (string $input, string $output) {
    $resolved = $this->action->execute(
        $this->typeResolver->resolve($input),
    );

    assertEquals($output, $resolved);
})->with('types');

it('can resolve self referencing types without current class', function () {
    $action = new TranspileTypeToTypeScriptAction($this->missingSymbols);

    assertEquals('{[key: string]: unknown}', $action->execute(new Self_()));
    assertEquals('{[key: string]: unknown}', $action->execute(new Static_()));
    assertEquals('{[key: string]: unknown}', $action->execute(new This()));
});

it('can resolve a struct type', function () {
    $transformed = $this->action->execute(StructType::fromArray([
        'a_string' => 'string',
        'a_float' => 'float',
        'a_class' => RegularEnum::class,
        'an_array' => 'int[]',
        'a_self_reference' => '$this',
        'an_object' => [
        'a_bool' => 'bool',
        'an_int' => 'int',
        ],
    ]));

    assertMatchesSnapshot($transformed);
    assertContains(RegularEnum::class, $this->missingSymbols->all());
    assertContains('fake_class', $this->missingSymbols->all());
});
