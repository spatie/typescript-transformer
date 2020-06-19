<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use Exception;
use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypescriptTransformer\Structures\Collection;
use Spatie\TypescriptTransformer\Tests\Fakes\FakeType;

class ReplaceSymbolsInTypeActionTest extends TestCase
{
    private Collection $collection;

    private ReplaceSymbolsInTypeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = Collection::create();

        $this->action = new ReplaceSymbolsInTypeAction($this->collection);
    }

    /** @test */
    public function it_can_replace_symbols()
    {
        $typeC = FakeType::create('C')
            ->isInline()
            ->withTransformed('This is type C');

        $typeB = FakeType::create('B')
            ->isInline()
            ->withMissingSymbols(['C' => 'C'])
            ->withTransformed('Depends on type C: {%C%}');

        $typeA = FakeType::create('A')
            ->isInline()
            ->withMissingSymbols(['B' => 'B'])
            ->withTransformed("Depends on type B: {%B%}");

        $this->collection->add($typeA)->add($typeB)->add($typeC);

        $transformed = $this->action->execute($typeA);

        $this->assertEquals('Depends on type B: Depends on type C: This is type C', $transformed);
        $this->assertEquals('Depends on type C: This is type C', $this->collection->find('B')->transformed);
        $this->assertEquals('This is type C', $this->collection->find('C')->transformed);
    }

    /** @test */
    public function it_will_throw_an_exception_when_doing_circular_dependencies()
    {
        $this->expectException(Exception::class);

        $typeA = FakeType::create('A')
            ->isInline()
            ->withMissingSymbols(['B' => 'B'])
            ->withTransformed("Depends on type B: {%B%}");

        $typeB = FakeType::create('B')
            ->isInline()
            ->withMissingSymbols(['A' => 'A'])
            ->withTransformed('Depends on type A: {%A%}');

        $this->collection->add($typeA)->add($typeB);

        $this->action->execute($typeA);
    }

    /** @test */
    public function it_can_replace_non_inline_types_circular()
    {
        $typeB = FakeType::create('B')
            ->withMissingSymbols(['A' => 'A'])
            ->withTransformed('Links to A: {%A%}');

        $typeA = FakeType::create('A')
            ->withMissingSymbols(['B' => 'B'])
            ->withTransformed('Links to B: {%B%}');

        $this->collection->add($typeA)->add($typeB);

        $transformedA = $this->action->execute($typeA);
        $transformedB = $this->action->execute($typeB);

        $this->assertEquals('Links to B: B', $transformedA);
        $this->assertEquals('Links to A: A', $transformedB);
    }

    /** @test */
    public function it_can_inline_multiple_dependencies()
    {
        $typeC = FakeType::create('C')
            ->isInline()
            ->withTransformed('This is type C');

        $typeB = FakeType::create('B')
            ->isInline()
            ->withMissingSymbols(['C' => 'C'])
            ->withTransformed('Depends on type C: {%C%}');

        $typeA = FakeType::create('A')
            ->isInline()
            ->withMissingSymbols(['B' => 'B', 'C' => 'C'])
            ->withTransformed('Depends on type B: {%B%} | depends on type C: {%C%}');

        $this->collection->add($typeA)->add($typeB)->add($typeC);

        $transformed = $this->action->execute($typeA);

        $this->assertEquals(
            'Depends on type B: Depends on type C: This is type C | depends on type C: This is type C',
            $transformed
        );
    }
}
