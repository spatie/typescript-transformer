<?php

use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\EventHandlers\FileUpdatedOrCreatedWatchEventHandler;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\TestSupport\AllClassTransformer;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeFileStructureFactory;
use Spatie\TypeScriptTransformer\Tests\TestSupport\UntransformableTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

it('adds a new transformed when a file is created with a transformable class', function () {
    $factory = new FakeFileStructureFactory();

    $path = $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileCreatedWatchEvent($path));

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($collection)->toHaveCount(1);
    expect($transformed->getName())->toBe('User');
});

it('replaces the transformed when a file is updated with changed content', function () {
    $factory = new FakeFileStructureFactory();

    $reference = $factory->getFakeFileReference('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $originalTransformed = transformSingle(SimpleClass::class, reference: $reference);

    $collection = new TransformedCollection([$originalTransformed]);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileUpdatedWatchEvent($factory->path('Models/User.php')));

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());

    expect($collection)->toHaveCount(1);
    expect($transformed->getName())->toBe('User');
    expect($collection->has($originalTransformed->reference))->toBeFalse();
});

it('does nothing when the file is updated but the transformed is unchanged', function () {
    $factory = new FakeFileStructureFactory();

    $path = $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    // First create
    $handler->handle(new FileCreatedWatchEvent($path));

    $transformed = $collection->get((new ClassStringReference('App\Models\User'))->getKey());
    $transformed->changed = false;

    // Update with same content
    $handler->handle(new FileUpdatedWatchEvent($path));

    expect($collection)->toHaveCount(1);
    expect($transformed->changed)->toBeFalse();
});

it('removes the original and requires complete rewrite when class is no longer transformable', function () {
    $factory = new FakeFileStructureFactory();

    $reference = $factory->getFakeFileReference('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $originalTransformed = transformSingle(SimpleClass::class, reference: $reference);

    $collection = new TransformedCollection([$originalTransformed]);

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new UntransformableTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileUpdatedWatchEvent($factory->path('Models/User.php')));

    expect($collection)->toHaveCount(0);
    expect($collection->hasChanges())->toBeTrue();
});

it('ignores files where class name does not match filename', function () {
    $factory = new FakeFileStructureFactory();

    $path = $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class Post {
    public string $title;
}');

    $collection = new TransformedCollection();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileCreatedWatchEvent($path));

    expect($collection)->toHaveCount(0);
});

it('ignores files with multiple classes', function () {
    $factory = new FakeFileStructureFactory();

    $path = $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}
class Post {
    public string $title;
}');

    $collection = new TransformedCollection();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileCreatedWatchEvent($path));

    expect($collection)->toHaveCount(0);
});

it('does nothing when a non-transformable file is created', function () {
    $factory = new FakeFileStructureFactory();

    $path = $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name;
}');

    $collection = new TransformedCollection();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new UntransformableTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileCreatedWatchEvent($path));

    expect($collection)->toHaveCount(0);
});

it('ignores files with invalid PHP syntax', function () {
    $factory = new FakeFileStructureFactory();

    $path = $factory->writeFile('Models/User.php', '<?php
namespace App\Models;
class User {
    public string $name
}');

    $collection = new TransformedCollection();

    $transformer = TypeScriptTransformer::create(
        TypeScriptTransformerConfigFactory::create()
            ->transformer(new AllClassTransformer())
            ->transformDirectories($factory->path())
            ->writer(new FlatModuleWriter())
            ->get()
    );

    $handler = new FileUpdatedOrCreatedWatchEventHandler($transformer, $collection, new PhpNodeCollection());

    $handler->handle(new FileCreatedWatchEvent($path));

    expect($collection)->toHaveCount(0);
});
