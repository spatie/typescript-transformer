<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use Exception;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

class ReplaceSymbolsInTypeActionTest extends TestCase
{
    private TypesCollection $collection;

    private ReplaceSymbolsInTypeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = TypesCollection::create();

        $this->action = new ReplaceSymbolsInTypeAction($this->collection);
    }

    /** @test */
    public function it_can_replace_symbols()
    {
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

        $this->assertEquals('Depends on type B: Depends on type C: This is type C', $transformed);
        $this->assertEquals('Depends on type C: This is type C', $this->collection['B']->transformed);
        $this->assertEquals('This is type C', $this->collection['C']->transformed);
    }

    /** @test */
    public function it_will_throw_an_exception_when_doing_circular_dependencies()
    {
        $this->expectException(Exception::class);

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
    }

    /** @test */
    public function it_can_replace_non_inline_types_circular()
    {
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

        $this->assertEquals('Links to B: B', $transformedA);
        $this->assertEquals('Links to A: A', $transformedB);
    }

    /** @test */
    public function it_can_inline_multiple_dependencies()
    {
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

        $this->assertEquals(
            'Depends on type B: Depends on type C: This is type C | depends on type C: This is type C',
            $transformed
        );
    }
}
