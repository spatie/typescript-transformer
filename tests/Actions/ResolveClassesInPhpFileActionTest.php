<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Actions\ResolveClassesInPhpFileAction;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeInterface;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeTrait;
use Symfony\Component\Finder\SplFileInfo;

class ResolveClassesInPhpFileActionTest extends TestCase
{
    private ResolveClassesInPhpFileAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ResolveClassesInPhpFileAction();
    }

    /** @test */
    public function it_can_find_classes()
    {
        $this->assertEquals([SomeClass::class,], $this->action->execute(
            new SplFileInfo(__DIR__ . '/../FakeClasses/Finder/SomeClass.php', '', '')
        ));
    }

    /** @test */
    public function it_can_find_interfaces()
    {
        $this->assertEquals([SomeInterface::class,], $this->action->execute(
            new SplFileInfo(__DIR__ . '/../FakeClasses/Finder/SomeInterface.php', '', '')
        ));
    }

    /** @test */
    public function it_can_find_traits()
    {
        $this->assertEquals([SomeTrait::class,], $this->action->execute(
            new SplFileInfo(__DIR__ . '/../FakeClasses/Finder/SomeTrait.php', '', '')
        ));
    }
}
