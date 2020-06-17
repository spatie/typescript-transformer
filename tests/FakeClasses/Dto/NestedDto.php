<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObject;

class NestedDto extends DataTransferObject
{
    public SimpleDto $instance;

    /** @var array|\Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\SimpleDto[] */
    public array $array;

    public SimpleDtoCollection $collection;
}
