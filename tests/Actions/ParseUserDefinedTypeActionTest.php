<?php

use Spatie\TypeScriptTransformer\Actions\ParseUserDefinedTypeAction;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;

it('can parse a user defined type', function () {
    $parser = new ParseUserDefinedTypeAction();

    expect($parser->execute('string'))->toBeInstanceOf(TypeScriptString::class);
    expect($parser->execute('array<int, string>'))->toEqual(new TypeScriptArray([new TypeScriptString()]));
    expect($parser->execute('self', PhpClassNode::fromReflection(new ReflectionClass(DateTime::class))))->toEqual(new TypeScriptReference(new ClassStringReference(DateTime::class)));
});
