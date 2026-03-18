<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\EventHandlers\DirectoryDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('removes transformed items from the deleted directory and its subdirectories', function () {
    $factory = new FakeFileStructureFactory();

    $parentDirTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );
    $subDirTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/Nested/Post.php'),
    );
    $otherDirTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Data/UserData.php'),
    );

    $collection = new TransformedCollection([
        $parentDirTransformed,
        $subDirTransformed,
        $otherDirTransformed,
    ]);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()->outputDirectory($this->temporaryDirectory->path())->get()
    );

    $handler = new DirectoryDeletedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $result = $handler->handle(new DirectoryDeletedWatchEvent($factory->path('Models')));

    expect($result)->toBeNull();
    expect($collection->has($parentDirTransformed->getReference()))->toBeFalse();
    expect($collection->has($subDirTransformed->getReference()))->toBeFalse();
    expect($collection->has($otherDirTransformed->getReference()))->toBeTrue();
});
