<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\SimpleDto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\SimpleDtoCollection;

class NestedDto extends DataTransferObject
{
    public SimpleDto $instance;

    /** @var array|\Spatie\TypescriptTransformer\Tests\FakeClasses\Dto\SimpleDto[] */
    public array $array;

    public SimpleDtoCollection $collection;
}
