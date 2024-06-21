<?php

use Spatie\TypeScriptTransformer\Actions\ParseUserDefinedTypeAction;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeReference;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;

it('can parse a user defined type', function () {
    $parser = new ParseUserDefinedTypeAction();

    expect($parser->execute('string'))->toBeInstanceOf(TypeScriptString::class);
    expect($parser->execute('array<int, string>'))->toEqual(new TypeScriptArray([new TypeScriptString()]));
    expect($parser->execute('self', new ReflectionClass(DateTime::class)))->toEqual(new TypeReference(new ClassStringReference(DateTime::class)));
});
