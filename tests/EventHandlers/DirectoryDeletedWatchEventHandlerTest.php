<?php

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\EventHandlers\DirectoryDeletedWatchEventHandler;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Support\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

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
        TypeScriptTransformerConfigFactory::create()->get()
    );

    $handler = new DirectoryDeletedWatchEventHandler($transformer, $collection);

    $result = $handler->handle(new DirectoryDeletedWatchEvent($factory->path('Models')));

    expect($result)->toBeNull();
    expect($collection->has($parentDirTransformed->reference))->toBeFalse();
    expect($collection->has($subDirTransformed->reference))->toBeFalse();
    expect($collection->has($otherDirTransformed->reference))->toBeTrue();
});
