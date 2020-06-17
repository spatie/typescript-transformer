<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObject;

class SimpleDto extends DataTransferObject
{
    public string $name;
}
