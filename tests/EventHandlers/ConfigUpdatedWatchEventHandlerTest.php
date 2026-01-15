<?php

use Spatie\TypeScriptTransformer\Data\WatchEventResult;
use Spatie\TypeScriptTransformer\EventHandlers\ConfigUpdatedWatchEventHandler;
use Spatie\TypeScriptTransformer\Events\FileUpdatedWatchEvent;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

it('returns null when the event path is not a config path', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->configPath('/path/to/config.php')
        ->get();

    $transformer = TypeScriptTransformer::create($config);
    $handler = new ConfigUpdatedWatchEventHandler($transformer);

    $result = $handler->handle(new FileUpdatedWatchEvent('/path/to/some/other/file.php'));

    expect($result)->toBeNull();
});

it('returns a complete refresh when the event path is a config path', function () {
    $config = TypeScriptTransformerConfigFactory::create()
        ->configPath('/path/to/config.php')
        ->get();

    $transformer = TypeScriptTransformer::create($config);
    $handler = new ConfigUpdatedWatchEventHandler($transformer);

    $result = $handler->handle(new FileUpdatedWatchEvent('/path/to/config.php'));

    expect($result)->toBeInstanceOf(WatchEventResult::class);
    expect($result->completeRefresh)->toBeTrue();
});
