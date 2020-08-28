<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration;

use Spatie\DataTransferObject\DataTransferObject;

/** @typescript */
class OtherDto extends DataTransferObject
{
    public string $name;
}
