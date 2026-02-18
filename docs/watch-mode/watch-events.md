---
title: Watch events
weight: 3
---

The package will now watch for file changes in the `transformDirectories` you've configured earlier. But what about
providers which provide additional transformed objects outside of the package transformation flow?

These providers can implement the `WatchingTransformedProvider` interface:

```php
use Spatie\TypeScriptTransformer\TransformedProviders\WatchingTransformedProvider;
use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\Events\SummarizedWatchEvent;

class CustomWatchableProvider implements WatchingTransformedProvider, TransformedProvider
{
    public function directoriesToWatch(): array
    {

        return [__DIR__.'/models'];
    }

    public function handleWatchEvent(WatchEvent $watchEvent, TransformedCollection $transformedCollection): ?WatchEventResult
    {
        if(! $watchEvent instanceof SummarizedWatchEvent){
            return null;
        }

        // Handle changes to the TransformedCollection
    }
}
```

The `directoriesToWatch` method should return an array of directories the provider wants to watch for changes. The
`handleWatchEvent` method is called when a change is detected in one of these directories.

There are a few possible types of watch events:

- `FileCreatedWatchEvent`: A file was created
- `FileUpdatedWatchEvent`: A file was updated
- `FileDeletedWatchEvent`: A file was deleted
- `DirectoryDeletedWatchEvent`: A directory was deleted

All of these events extend the `WatchEvent` class and contain the path of the changed file or directory.

In the end when all events have been processed, a `SummarizedWatchEvent` is dispatched containing all the created,
updated and deleted files and directories.

When everything went well, the `handleWatchEvent` method should return null or WatchEventResult::continue(). When
your provider needs an updated application state e.g. a complete worker restart, it should return WatchEventResult::
completeRefresh().

Please note that when a complete refresh is requested, the whole worker process will be restarted which could take a
few seconds depending on the size of your application. In order to counter this we, for example, in the Laravel package
needed a complete new application state for the routes helper provider since it needs to fetch all routes from the
router. Instead of requesting a complete refresh on every route change, we created a custom command returning all the
routes as JSON allowing us to update the routes without restarting the whole application by calling this command within
the `handleWatchEvent` method.

## Updating the transformed collection

Within the `handleWatchEvent` method you receive the current `TransformedCollection` which you can update based on the
detected changes. Since a lot of the transformed objects within the collection are referenced by each other it can
become quite complex to make sure everything is updated correctly.

That's why the collection provides a few methods to make this process easier:

**public function add(Transformed ...$transformed): void**

Allows you to add new transformed objects to the collection, if a transformed object with the same reference already
exists it will be replaced.

**public function has(Reference|string $reference): bool**

Allows you to check whether a transformed object with the given reference exists in the collection.

**public function get(Reference|string $reference): ?Transformed**

Allows you to get a transformed object by its reference, returns null when no transformed object was found?

**public function remove(Reference|string $reference): void**

Removes a transformed object from the collection by its reference. If that transformed object is referenced by other
transformed objects these references will be removed as well and tagged to be missing. If later on a new transformed
object is added with the same reference these missing references will be resolved automatically.

**public function findTransformedByFile(string $path): ?Transformed**

Allows you to find the current transformed object for a given file path, useful when a file was updated. The way this
works is that each Reference implementation can implement the `FilesystemReference` interface which provides the file
path of the referenced object.

**public function findTransformedByDirectory(string $path): Generator**

Allows you to find all transformed objects within a given directory.

**public function requireCompleteRewrite(): void**

While we try to cache the output of transformed objects as much as possible, only invalidating changed objects and the
objects that reference changed objects, sometimes a complete rewrite is necessary. You can request such a complete
rewrite
by calling this method.
