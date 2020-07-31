<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use PhpParser\Lexer;
use PhpParser\Parser\Php7;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\AnonymousClassObjectSourceLocator;
use Spatie\TypescriptTransformer\Actions\ResolveClassPropertyAction;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Test;

class ResolveClassPropertyActionTest extends TestCase
{
    private ResolveClassPropertyAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ResolveClassPropertyAction();
    }

    /** @test */
    public function it_can_deduce_types()
    {
        $class = new class {
            /** @var array<int, array<int, string>>  */
            public array $propertyB;
        };

        $this->action->execute($this->createReflectionProperty($class));
    }

    private function createReflectionProperty(object $class): ReflectionProperty
    {
        $reflector = new ClassReflector(
            new AnonymousClassObjectSourceLocator($class, new Php7(new Lexer()))
        );

        $reflectionClass = $reflector->reflect(get_class($class));

        return current($reflectionClass->getProperties());
    }
}
