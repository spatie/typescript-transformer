<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use PhpParser\Lexer;
use PhpParser\Parser\Php7;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\AnonymousClassObjectSourceLocator;
use Spatie\TypescriptTransformer\Actions\ResolveClassPropertyTypeAction;
use Spatie\TypescriptTransformer\Actions\TransformClassPropertyTypeAction;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Test;

class ResolveClassPropertyActionTest extends TestCase
{
    private ResolveClassPropertyTypeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ResolveClassPropertyTypeAction(
            new TypeResolver()
        );
    }

    /** @test */
    public function it_can_deduce_types()
    {
        $this->markTestIncomplete();

        $class = new class {
            /** @var static */
            public $propertyB;
        };
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
