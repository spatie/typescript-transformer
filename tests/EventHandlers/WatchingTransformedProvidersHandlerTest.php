<?php

use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\EventHandlers\WatchingTransformedProvidersHandler;
use Spatie\TypeScriptTransformer\Events\DirectoryDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileCreatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileDeletedWatchEvent;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;
use Spatie\TypeScriptTransformer\Tests\TestSupport\FakeWatchingTransformedProvider;

it('only passes events to provider when path matches watched directory', function ($matchingEvent, $nonMatchingEvent) {
    $provider = new FakeWatchingTransformedProvider(
        directoriesToWatch: ['/app/Models'],
    );
    $collection = new TransformedCollection();
    $handler = new WatchingTransformedProvidersHandler($provider, $collection);

    $handler->handle($nonMatchingEvent);

    expect($provider->receivedEvents)->toBeEmpty();

    $handler->handle($matchingEvent);

    expect($provider->receivedEvents)->toHaveCount(1);
    expect($provider->receivedEvents[0])->toBe($matchingEvent);
})->with([
    'FileCreatedWatchEvent' => [
        new FileCreatedWatchEvent('/app/Models/User.php'),
        new FileCreatedWatchEvent('/app/Controllers/UserController.php'),
    ],
    'FileUpdatedWatchEvent' => [
        new FileUpdatedWatchEvent('/app/Models/User.php'),
        new FileUpdatedWatchEvent('/app/Controllers/UserController.php'),
    ],
    'FileDeletedWatchEvent' => [
        new FileDeletedWatchEvent('/app/Models/User.php'),
        new FileDeletedWatchEvent('/app/Controllers/UserController.php'),
    ],
    'DirectoryDeletedWatchEvent' => [
        new DirectoryDeletedWatchEvent('/app/Models/Nested'),
        new DirectoryDeletedWatchEvent('/app/Controllers'),
    ],
    'SummarizedWatchEvent with createdFiles' => [
        new SummarizedWatchEvent(createdFiles: ['/app/Models/User.php']),
        new SummarizedWatchEvent(createdFiles: ['/app/Controllers/UserController.php']),
    ],
    'SummarizedWatchEvent with updatedFiles' => [
        new SummarizedWatchEvent(updatedFiles: ['/app/Models/User.php']),
        new SummarizedWatchEvent(updatedFiles: ['/app/Controllers/UserController.php']),
    ],
    'SummarizedWatchEvent with deletedFiles' => [
        new SummarizedWatchEvent(deletedFiles: ['/app/Models/User.php']),
        new SummarizedWatchEvent(deletedFiles: ['/app/Controllers/UserController.php']),
    ],
    'SummarizedWatchEvent with deletedDirectories' => [
        new SummarizedWatchEvent(deletedDirectories: ['/app/Models/Nested']),
        new SummarizedWatchEvent(deletedDirectories: ['/app/Controllers']),
    ],
]);
