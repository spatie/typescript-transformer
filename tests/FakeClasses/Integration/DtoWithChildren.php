<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration;

/** @typescript */
class DtoWithChildren
{
    public string $name;

    public OtherDto $other_dto;

    /** @var \Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto[] */
    public array $other_dto_array;
}
