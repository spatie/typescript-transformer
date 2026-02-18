<?php

use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\EventHandlers\FileDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Support\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

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
        TypeScriptTransformerConfigFactory::create()->get()
    );

    $handler = new FileDeletedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $result = $handler->handle(new FileDeletedWatchEvent($factory->path('Models/User.php')));

    expect($result)->toBeNull();
    expect($collection->has($deletedFileTransformed->reference))->toBeFalse();
    expect($collection->has($otherFileTransformed->reference))->toBeTrue();
});

it('does nothing when the deleted file has no transformed item', function () {
    $factory = new FakeFileStructureFactory();

    $existingTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );

    $collection = new TransformedCollection([$existingTransformed]);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()->get()
    );

    $handler = new FileDeletedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $result = $handler->handle(new FileDeletedWatchEvent($factory->path('Models/UnknownFile.php')));

    expect($result)->toBeNull();
    expect($collection->has($existingTransformed->reference))->toBeTrue();
});
