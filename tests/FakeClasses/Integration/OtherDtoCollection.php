<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration;

use Spatie\DataTransferObject\DataTransferObjectCollection;

/** @typescript */
class OtherDtoCollection extends DataTransferObjectCollection
{
    public function current(): OtherDto
    {
        return parent::current();
    }
}
