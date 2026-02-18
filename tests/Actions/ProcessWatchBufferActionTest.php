<?php

use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\ProcessWatchBufferAction;
use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularA;
use Spatie\TypeScriptTransformer\Tests\Fakes\Circular\CircularB;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeWatchingTransformedProvider;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

it('returns continue when there are no events', function () {
    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()->writer($writer)->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $result = $action->execute([]);

    expect($result)->toBeNull();
});

it('processes file created events and adds transformed items', function () {
    $factory = new FakeFileStructureFactory();
    $temporaryDirectory = TemporaryDirectory::make();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->outputDirectory($temporaryDirectory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $result = $action->execute([
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result)->toBeNull();

    expect($collection)->toHaveCount(1);

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($transformed->getName())->toBe('User');
    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier('User'),
            new TypeScriptObject([
                new TypeScriptProperty('name', new TypeScriptString()),
            ])
        )
    );
});

it('processes file updated events and updates transformed items', function () {
    $factory = new FakeFileStructureFactory();
    $temporaryDirectory = TemporaryDirectory::make();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->outputDirectory($temporaryDirectory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $action->execute([
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($collection)->toHaveCount(1);

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($transformed->getName())->toBe('User');
    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier('User'),
            new TypeScriptObject([
                new TypeScriptProperty('name', new TypeScriptString()),
            ])
        )
    );

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $username;
}');

    $result = $action->execute([
        new FileUpdatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result)->toBeNull();
    expect($collection)->toHaveCount(1);

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($transformed->getName())->toBe('User');
    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier('User'),
            new TypeScriptObject([
                new TypeScriptProperty('username', new TypeScriptString()),
            ])
        )
    );
});


it('processes file deleted events and removes transformed items', function () {
    $factory = new FakeFileStructureFactory();

    $deletedFileTransformed = transformSingle(
        SimpleClass::class,
        reference: $factory->getFakeFileReference('Models/User.php'),
    );

    $collection = new TransformedCollection([$deletedFileTransformed]);
    $writer = new FlatModuleWriter();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $result = $action->execute([
        new FileDeletedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result)->toBeNull();
    expect($collection)->toHaveCount(0);
});

it('processes directory deleted events and removes all transformed items in directory', function () {
    $factory = new FakeFileStructureFactory();
    $temporaryDirectory = TemporaryDirectory::make();
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

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->outputDirectory($temporaryDirectory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $result = $action->execute([
        new DirectoryDeletedWatchEvent($factory->path('Models')),
    ]);

    expect($result)->toBeNull();
    expect($collection->has($parentDirTransformed->reference))->toBeFalse();
    expect($collection->has($subDirTransformed->reference))->toBeFalse();
    expect($collection->has($otherDirTransformed->reference))->toBeTrue();
});

it('returns complete refresh when config file is updated', function () {
    $factory = new FakeFileStructureFactory();
    $configPath = $factory->writeFile('config.php', '<?php return [];');

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->configPath($configPath)
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

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

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->configPath($configPath)
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $result = $action->execute([
        new FileUpdatedWatchEvent($configPath),
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
    ]);

    expect($result)->toBeInstanceOf(WatchEventResult::class);
    expect($result->completeRefresh)->toBeTrue();
    expect($collection)->toHaveCount(0);
});

it('calls watching transformed providers with summarized event at the end', function () {
    $factory = new FakeFileStructureFactory();
    $temporaryDirectory = TemporaryDirectory::make();

    $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $watchingProvider = new FakeWatchingTransformedProvider(
        directoriesToWatch: [$factory->path()],
    );

    $collection = new TransformedCollection();
    $writer = new FlatModuleWriter();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->outputDirectory($temporaryDirectory->path())
            ->provider($watchingProvider)
            ->writer($writer)
            ->get()
    );

    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $result = $action->execute([
        new FileCreatedWatchEvent($factory->path('Models/User.php')),
        new FileUpdatedWatchEvent($factory->path('Models/User.php')),
        new FileDeletedWatchEvent($factory->path('Models/Post.php')),
        new DirectoryDeletedWatchEvent($factory->path('Data')),
    ]);

    expect($result)->toBeNull();

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
    expect($summarizedEvent->deletedDirectories)->toContain($factory->path('Data'));
});

it('can delete and create a referenced transformed and it will reconnect references', function () {
    $temporaryDirectory = TemporaryDirectory::make();
    $writer = new FlatModuleWriter();

    $circularAPath = dirname(__DIR__) . '/Fakes/Circular/CircularA.php';
    $circularBPath = dirname(__DIR__) . '/Fakes/Circular/CircularB.php';

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories(dirname(__DIR__) . '/Fakes/Circular')
            ->outputDirectory($temporaryDirectory->path())
            ->writer($writer)
            ->get()
    );

    $collection = new TransformedCollection();
    $action = new ProcessWatchBufferAction($transformer, $collection, new PhpNodeCollection());

    $action->execute([
        new FileCreatedWatchEvent($circularAPath),
        new FileCreatedWatchEvent($circularBPath),
    ]);

    expect($collection)->toHaveCount(2);

    $circularAKey = (new ClassStringReference(CircularA::class))->getKey();
    $circularBKey = (new ClassStringReference(CircularB::class))->getKey();

    $circularATransformed = $collection->get($circularAKey);
    $circularBTransformed = $collection->get($circularBKey);

    expect($circularATransformed->references)->toHaveKey($circularBKey);
    expect($circularATransformed->referencedBy)->toContain($circularBKey);

    expect($circularBTransformed->references)->toHaveKey($circularAKey);
    expect($circularBTransformed->referencedBy)->toContain($circularAKey);

    assertMatchesSnapshot(file_get_contents($temporaryDirectory->path('types.ts')));

    $action->execute([
        new FileDeletedWatchEvent($circularAPath),
    ]);

    expect($collection)->toHaveCount(1);
    expect($collection->has($circularAKey))->toBeFalse();

    $circularBTransformed = $collection->get($circularBKey);

    expect($circularBTransformed->referencedBy)->not->toContain($circularAKey);
    expect($circularBTransformed->missingReferences)->toHaveKey($circularAKey);

    assertMatchesSnapshot(file_get_contents($temporaryDirectory->path('types.ts')));

    $action->execute([
        new FileCreatedWatchEvent($circularAPath),
    ]);

    expect($collection)->toHaveCount(2);

    $recreatedCircularATransformed = $collection->get($circularAKey);
    $updatedCircularBTransformed = $collection->get($circularBKey);

    expect($recreatedCircularATransformed->references)->toHaveKey($circularBKey);
    expect($updatedCircularBTransformed->references)->toHaveKey($circularAKey);

    expect($recreatedCircularATransformed->referencedBy)->toContain($circularBKey);
    expect($updatedCircularBTransformed->referencedBy)->toContain($circularAKey);

    assertMatchesSnapshot(file_get_contents($temporaryDirectory->path('types.ts')));
});
