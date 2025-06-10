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
        false,
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

    assertEquals('any', $action->execute(new Self_()));
    assertEquals('any', $action->execute(new Static_()));
    assertEquals('any', $action->execute(new This()));
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

it('can resolve pseudo types', function () {
    $transformed = $this->action->execute($this->typeResolver->resolve('array-key'));

    expect($transformed->typescript)->toBe('string | number');
});

it('does not add nullable unions to optional properties', function () {
    $action = new TranspileTypeToTypeScriptAction(
        $this->missingSymbols,
        true
    );

    $transformed = $action->execute(StructType::fromArray([
        'a_string' => 'string',
        'a_nullable_string' => '?string',
    ]));

    assertEquals('{a_string:string;a_nullable_string:string;}', $transformed);
});
