<?php


use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInTypeAction;
use Spatie\TypeScriptTransformer\Actions\ResolveClassesInPhpFileAction;

use function PHPUnit\Framework\assertInstanceOf;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;

it('can override actions', function () {
    
    //Default resolultion works.
   $instance = TypeScriptTransformer::make(ReplaceSymbolsInCollectionAction::class);
    assertInstanceOf(ReplaceSymbolsInCollectionAction::class, $instance);

    //Can be overridden
    TypescriptTransformer::override(ReplaceSymbolsInCollectionAction::class, ResolveClassesInPhpFileAction::class);
    $instance = TypeScriptTransformer::make(ReplaceSymbolsInCollectionAction::class);
    assertInstanceOf(ResolveClassesInPhpFileAction::class, $instance);

    //Can be set back to default.
    TypeScriptTransformer::override(ReplaceSymbolsInCollectionAction::class, ReplaceSymbolsInCollectionAction::class);
    $instance = TypeScriptTransformer::make(ReplaceSymbolsInCollectionAction::class);
    assertInstanceOf(ReplaceSymbolsInCollectionAction::class, $instance);
   
});

