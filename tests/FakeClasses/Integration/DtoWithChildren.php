<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration;

use Spatie\DataTransferObject\DataTransferObject;

/** @typescript */
class DtoWithChildren extends DataTransferObject
{
    public string $name;

    public OtherDto $other_dto;

    /** @var \Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto[] */
    public array $other_dto_array;
}
