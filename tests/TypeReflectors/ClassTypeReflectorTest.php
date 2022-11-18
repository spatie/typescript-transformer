<?php

use phpDocumentor\Reflection\Type;
use function PHPUnit\Framework\assertEquals;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionClass;
use Spatie\TypeScriptTransformer\TypeReflectors\ClassTypeReflector;

it('can correctly reflect classes', function (
    FakeReflectionClass | ReflectionClass $reflection,
    bool $transformable,
    bool $inline,
    ?string $name,
    ?string $transformer,
    ?Type $type
) {
    $reflected = ClassTypeReflector::create($reflection);

    assertEquals($transformable, $reflected->isTransformable());
    assertEquals($inline, $reflected->isInline());
    assertEquals($name, $reflected->getName());
    assertEquals($transformer, $reflected->getTransformerClass());
    assertEquals($type, $reflected->getType());
})->with('reflection_classes');
