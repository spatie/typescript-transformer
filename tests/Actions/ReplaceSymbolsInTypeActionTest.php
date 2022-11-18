<?php

use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypeScriptTransformer\Exceptions\CircularDependencyChain;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

beforeEach(function () {
    $this->collection = TypesCollection::create();

    $this->action = new ReplaceSymbolsInTypeAction($this->collection);
});

it('can replace symbols', function () {
    $typeC = FakeTransformedType::fake('C')
        ->isInline()
        ->withTransformed('This is type C');

    $typeB = FakeTransformedType::fake('B')
        ->isInline()
        ->withMissingSymbols(['C' => 'C'])
        ->withTransformed('Depends on type C: {%C%}');

    $typeA = FakeTransformedType::fake('A')
        ->isInline()
        ->withMissingSymbols(['B' => 'B'])
        ->withTransformed("Depends on type B: {%B%}");

    $this->collection[] = $typeA;
    $this->collection[] = $typeB;
    $this->collection[] = $typeC;

    $transformed = $this->action->execute($typeA);

    assertEquals('Depends on type B: Depends on type C: This is type C', $transformed);
    assertEquals('Depends on type C: This is type C', $this->collection['B']->transformed);
    assertEquals('This is type C', $this->collection['C']->transformed);
});

it('will throw an exception when doing circular dependencies', function () {
    $this->expectException(CircularDependencyChain::class);

    $typeA = FakeTransformedType::fake('A')
        ->isInline()
        ->withMissingSymbols(['B' => 'B'])
        ->withTransformed("Depends on type B: {%B%}");

    $typeB = FakeTransformedType::fake('B')
        ->isInline()
        ->withMissingSymbols(['A' => 'A'])
        ->withTransformed('Depends on type A: {%A%}');

    $this->collection[] = $typeA;
    $this->collection[] = $typeB;

    $this->action->execute($typeA);
});

it('can replace non inline types circular', function () {
    $typeB = FakeTransformedType::fake('B')
        ->withMissingSymbols(['A' => 'A'])
        ->withTransformed('Links to A: {%A%}');

    $typeA = FakeTransformedType::fake('A')
        ->withMissingSymbols(['B' => 'B'])
        ->withTransformed('Links to B: {%B%}');

    $this->collection[] = $typeA;
    $this->collection[] = $typeB;

    $transformedA = $this->action->execute($typeA);
    $transformedB = $this->action->execute($typeB);

    assertEquals('Links to B: B', $transformedA);
    assertEquals('Links to A: A', $transformedB);
});

it('can inline multiple dependencies', function () {
    $typeC = FakeTransformedType::fake('C')
        ->isInline()
        ->withTransformed('This is type C');

    $typeB = FakeTransformedType::fake('B')
        ->isInline()
        ->withMissingSymbols(['C' => 'C'])
        ->withTransformed('Depends on type C: {%C%}');

    $typeA = FakeTransformedType::fake('A')
        ->isInline()
        ->withMissingSymbols(['B' => 'B', 'C' => 'C'])
        ->withTransformed('Depends on type B: {%B%} | depends on type C: {%C%}');

    $this->collection[] = $typeA;
    $this->collection[] = $typeB;
    $this->collection[] = $typeC;

    $transformed = $this->action->execute($typeA);

    assertEquals(
        'Depends on type B: Depends on type C: This is type C | depends on type C: This is type C',
        $transformed
    );
});
