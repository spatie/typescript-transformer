<?php

use Spatie\TypeScriptTransformer\Tests\Factories\TransformedFactory;
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
    $typeC = TransformedFactory::create('C')
        ->isInline()
        ->withTransformed('This is type C')
        ->build();

    $typeB = TransformedFactory::create('B')
        ->isInline()
        ->withTypeReferences('C')
        ->withTransformed('Depends on type C: {%C%}')
        ->build();

    $typeA = TransformedFactory::create('A')
        ->isInline()
        ->withTypeReferences('B')
        ->withTransformed("Depends on type B: {%B%}")
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);
    $this->collection->add($typeC);

    $this->action->execute($typeA);

    assertEquals('Depends on type B: Depends on type C: This is type C', $this->collection->get('A')->toString());
    assertEquals('Depends on type C: This is type C', $this->collection->get('B')->toString());
    assertEquals('This is type C', $this->collection->get('C')->toString());
});

it('will throw an exception when doing circular dependencies', function () {
    $this->expectException(CircularDependencyChain::class);

    $typeA = TransformedFactory::create('A')
        ->isInline()
        ->withTypeReferences('B')
        ->withTransformed("Depends on type B: {%B%}")
        ->build();

    $typeB = TransformedFactory::create('B')
        ->isInline()
        ->withTypeReferences('A')
        ->withTransformed('Depends on type A: {%A%}')
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);

    $this->action->execute($typeA);
});

it('can replace non inline types circular', function () {
    $typeB = TransformedFactory::create('B')
        ->withTypeReferences('A')
        ->withTransformed('Links to A: {%A%}')
        ->build();

    $typeA = TransformedFactory::create('A')
        ->withTypeReferences('B')
        ->withTransformed('Links to B: {%B%}')
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);

    $this->action->execute($typeA);
    $this->action->execute($typeB);

    assertEquals('Links to B: B', $this->collection->get('A')->toString());
    assertEquals('Links to A: A', $this->collection->get('B')->toString());
});

it('can inline multiple dependencies', function () {
    $typeC = TransformedFactory::create('C')
        ->isInline()
        ->withTransformed('This is type C')
        ->build();

    $typeB = TransformedFactory::create('B')
        ->isInline()
        ->withTypeReferences('C')
        ->withTransformed('Depends on type C: {%C%}')
        ->build();

    $typeA = TransformedFactory::create('A')
        ->isInline()
        ->withTypeReferences('B', 'C')
        ->withTransformed('Depends on type B: {%B%} | depends on type C: {%C%}')
        ->build();

    $this->collection->add($typeA);
    $this->collection->add($typeB);
    $this->collection->add($typeC);

    $this->action->execute($typeA);

    assertEquals(
        'Depends on type B: Depends on type C: This is type C | depends on type C: This is type C',
        $this->collection->get('A')->toString()
    );
});
