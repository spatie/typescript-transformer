<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class LoadPhpClassNodeAction
{
    protected Locator $astLocator;

    protected AutoloadSourceLocator $autoSourceLocator;

    protected PhpInternalSourceLocator $phpInternalSourceLocator;

    public function __construct()
    {
        $this->astLocator = (new BetterReflection())->astLocator();
        $this->autoSourceLocator = new AutoloadSourceLocator($this->astLocator);
        $this->phpInternalSourceLocator = new PhpInternalSourceLocator($this->astLocator, new ReflectionSourceStubber());
    }

    public function execute(
        string $path
    ): ?PhpClassNode {
        $reflector = $this->resolveReflector($path);

        $classes = $reflector->reflectAllClasses();

        if (count($classes) === 1) {
            return PhpClassNode::fromReflection($classes[0]);
        }

        return null;
    }


    protected function resolveReflector(string $path): DefaultReflector
    {
        return new DefaultReflector(new AggregateSourceLocator([
            new SingleFileSourceLocator($path, $this->astLocator),
            $this->autoSourceLocator,
            $this->phpInternalSourceLocator,
        ]));
    }
}
