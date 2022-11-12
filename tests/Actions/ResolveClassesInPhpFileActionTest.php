<?php

use Spatie\TypeScriptTransformer\Actions\ResolveClassesInPhpFileAction;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeClass;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeInterface;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Finder\SomeTrait;
use Symfony\Component\Finder\SplFileInfo;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    $this->action = new ResolveClassesInPhpFileAction();
});

it('can find classes', function () {
    assertEquals([SomeClass::class,], $this->action->execute(
        new SplFileInfo(__DIR__ . '/../FakeClasses/Finder/SomeClass.php', '', '')
    ));
});

it('can find interfaces', function () {
    assertEquals([SomeInterface::class,], $this->action->execute(
        new SplFileInfo(__DIR__ . '/../FakeClasses/Finder/SomeInterface.php', '', '')
    ));
});

it('can find traits', function () {
    assertEquals([SomeTrait::class,], $this->action->execute(
        new SplFileInfo(__DIR__ . '/../FakeClasses/Finder/SomeTrait.php', '', '')
    ));
});

it('can find enums', function () {
    assertEquals([SomeEnum::class,], $this->action->execute(
        new SplFileInfo(__DIR__.'./../FakeClasses/Finder/SomeEnum.php', '', '')
    ));
});
