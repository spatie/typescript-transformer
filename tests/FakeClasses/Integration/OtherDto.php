<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Integration;

use Spatie\DataTransferObject\DataTransferObject;

/** @typescript */
class OtherDto extends DataTransferObject
{
    public string $name;
}
