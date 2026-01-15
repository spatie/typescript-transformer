<?php

use Spatie\TypeScriptTransformer\Actions\ProcessWatchBufferAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Collections\WritersCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Tests\Support\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\Tests\Support\FakeWatchingTransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

it('returns continue when there are no events', function () {
    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([]);

    expect($result)->toBeInstanceOf(WatchEventResult::class);
    expect($result->completeRefresh)->toBeFalse();
});

it('processes file created events and adds transformed items', function () {
    $factory = new FakeFileStructureFactory();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result->completeRefresh)->toBeFalse();
    expect($collection)->toHaveCount(1);

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($transformed->getName())->toBe('User');
});

it('processes file updated events and updates transformed items', function () {
    $factory = new FakeFileStructureFactory();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileUpdatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result->completeRefresh)->toBeFalse();
    expect($collection)->toHaveCount(1);

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($transformed->getName())->toBe('User');
});

it('processes file deleted events and removes transformed items', function () {
    $factory = new FakeFileStructureFactory();

    $deletedFileTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );

    $collection = new TransformedCollection([$deletedFileTransformed]);
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileDeletedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result->completeRefresh)->toBeFalse();
    expect($collection)->toHaveCount(0);
});

it('processes directory deleted events and removes all transformed items in directory', function () {
    $factory = new FakeFileStructureFactory();
    $writer = new FlatModuleWriter();

    $parentDirTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );
    $parentDirTransformed->setWriter($writer);

    $subDirTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/Nested/Post.php'),
    );
    $subDirTransformed->setWriter($writer);

    $otherDirTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Data/UserData.php'),
    );
    $otherDirTransformed->setWriter($writer);

    $collection = new TransformedCollection([
        $parentDirTransformed,
        $subDirTransformed,
        $otherDirTransformed,
    ]);
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new DirectoryDeletedWatchEvent($factory->path('Models')),
    ]);

    expect($result->completeRefresh)->toBeFalse();
    expect($collection->has($parentDirTransformed->reference))->toBeFalse();
    expect($collection->has($subDirTransformed->reference))->toBeFalse();
    expect($collection->has($otherDirTransformed->reference))->toBeTrue();
});

it('returns complete refresh when config file is updated', function () {
    $factory = new FakeFileStructureFactory();
    $configPath = $factory->writeFile('config.php', '<?php return [];');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->configPath($configPath)
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileUpdatedWatchEvent($configPath),
    ]);

    expect($result)->toBeInstanceOf(WatchEventResult::class);
    expect($result->completeRefresh)->toBeTrue();
});

it('stops processing and returns complete refresh immediately when encountered', function () {
    $factory = new FakeFileStructureFactory();

    $configPath = $factory->writeFile('config.php', '<?php return [];');

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->configPath($configPath)
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileUpdatedWatchEvent($configPath),
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result)->toBeInstanceOf(WatchEventResult::class);
    expect($result->completeRefresh)->toBeTrue();
    expect($collection)->toHaveCount(0);
});

it('processes multiple events in order', function () {
    $factory = new FakeFileStructureFactory();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $factory->writeFile('Models/Post.php', '<?php
namespace App\Models;
class Post {
    public string $title;
}');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
        new FileCreatedWatchEvent($factory->path('Models/Post.php')),
    ]);

    expect($result->completeRefresh)->toBeFalse();
    expect($collection)->toHaveCount(2);

    $userTransformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());
    $postTransformed = $collection->get((new ClassStringReference('App\Models\Post'))->getKey());

    expect($userTransformed->getName())->toBe('User');
    expect($postTransformed->getName())->toBe('Post');
});

it('calls watching transformed providers with summarized event at the end', function () {
    $factory = new FakeFileStructureFactory();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $watchingProvider = new FakeWatchingTransformedProvider();

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();
    $writersCollection = new WritersCollection($writer);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->provider($watchingProvider)
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, $writersCollection);

    $result = $action->execute([
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
        new FileUpdatedWatchEvent($factory->path('Models/User.php')),
        new FileDeletedWatchEvent($factory->path('Models/Post.php')),
    ]);

    expect($result->completeRefresh)->toBeFalse();

    $summarizedEvent = null;
    foreach ($watchingProvider->receivedEvents as $event) {
        if ($event instanceof SummarizedWatchEvent) {
            $summarizedEvent = $event;
        }
    }

    expect($summarizedEvent)->not->toBeNull();
    expect($summarizedEvent->createdFiles)->toContain($factory->path('Models/User.php'));
    expect($summarizedEvent->updatedFiles)->toContain($factory->path('Models/User.php'));
    expect($summarizedEvent->deletedFiles)->toContain($factory->path('Models/Post.php'));
});
