<?php

use Spatie\TypeScriptTransformer\Tests\Factories\TransformedTypeFactory;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Actions\ReplaceTypeReferencesInTypeAction;
use Spatie\TypeScriptTransformer\Exceptions\CircularDependencyChain;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

beforeEach(function () {
    $this->collection = TypesCollection::create();

    $this->action = new ReplaceTypeReferencesInTypeAction($this->collection);
});

it('can replace symbols', function () {
    $typeC = TransformedTypeFactory::create('C')
        ->isInline()
        ->withTransformed('This is type C')
        ->build();

    $typeB = TransformedTypeFactory::create('B')
        ->isInline()
        ->withTypeReferences('C')
        ->withTransformed('Depends on type C: {%C%}')
        ->build();

    $typeA = TransformedTypeFactory::create('A')
        ->isInline()
        ->withTypeReferences('B')
        ->withTransformed("Depends on type B: {%B%}")
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);
    $this->collection->add($typeC);

    $transformed = $this->action->execute($typeA);

    assertEquals('Depends on type B: Depends on type C: This is type C', $transformed);
    assertEquals('Depends on type C: This is type C', $this->collection->get('B')->transformed);
    assertEquals('This is type C', $this->collection->get('C')->transformed);
});

it('will throw an exception when doing circular dependencies', function () {
    $this->expectException(CircularDependencyChain::class);

    $typeA = TransformedTypeFactory::create('A')
        ->isInline()
        ->withTypeReferences('B')
        ->withTransformed("Depends on type B: {%B%}")
        ->build();

    $typeB = TransformedTypeFactory::create('B')
        ->isInline()
        ->withTypeReferences('A')
        ->withTransformed('Depends on type A: {%A%}')
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);

    $this->action->execute($typeA);
});

it('can replace non inline types circular', function () {
    $typeB = TransformedTypeFactory::create('B')
        ->withTypeReferences('A')
        ->withTransformed('Links to A: {%A%}')
        ->build();

    $typeA = TransformedTypeFactory::create('A')
        ->withTypeReferences('B')
        ->withTransformed('Links to B: {%B%}')
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);

    $transformedA = $this->action->execute($typeA);
    $transformedB = $this->action->execute($typeB);

    assertEquals('Links to B: B', $transformedA);
    assertEquals('Links to A: A', $transformedB);
});

it('can inline multiple dependencies', function () {
    $typeC = TransformedTypeFactory::create('C')
        ->isInline()
        ->withTransformed('This is type C')
        ->build();

    $typeB = TransformedTypeFactory::create('B')
        ->isInline()
        ->withTypeReferences('C')
        ->withTransformed('Depends on type C: {%C%}')
        ->build();

    $typeA = TransformedTypeFactory::create('A')
        ->isInline()
        ->withTypeReferences('B', 'C')
        ->withTransformed('Depends on type B: {%B%} | depends on type C: {%C%}')
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);
    $this->collection->add($typeC);

    $transformed = $this->action->execute($typeA);

    assertEquals(
        'Depends on type B: Depends on type C: This is type C | depends on type C: This is type C',
        $transformed
    );
});
