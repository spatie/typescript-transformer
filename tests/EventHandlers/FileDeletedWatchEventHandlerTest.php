<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\EventHandlers\FileDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

beforeEach(function () {
    $this->temporaryDirectory = TemporaryDirectory::make();
});

it('removes the transformed item when its file is deleted', function () {
    $factory = new FakeFileStructureFactory();

    $deletedFileTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );
    $otherFileTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/Post.php'),
    );

    $collection = new TransformedCollection([
        $deletedFileTransformed,
        $otherFileTransformed,
    ]);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()->outputDirectory($this->temporaryDirectory->path())->get()
    );

    $handler = new FileDeletedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $result = $handler->handle(new FileDeletedWatchEvent($factory->path('Models/User.php')));

    expect($result)->toBeNull();
    expect($collection->has($deletedFileTransformed->getReference()))->toBeFalse();
    expect($collection->has($otherFileTransformed->getReference()))->toBeTrue();
});

it('does nothing when the deleted file has no transformed item', function () {
    $factory = new FakeFileStructureFactory();

    $existingTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );

    $collection = new TransformedCollection([$existingTransformed]);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()->outputDirectory($this->temporaryDirectory->path())->get()
    );

    $handler = new FileDeletedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $result = $handler->handle(new FileDeletedWatchEvent($factory->path('Models/UnknownFile.php')));

    expect($result)->toBeNull();
    expect($collection->has($existingTransformed->getReference()))->toBeTrue();
});
