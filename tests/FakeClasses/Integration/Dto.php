<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Integration;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;

/** @typescript */
class Dto extends DataTransferObject
{
    public OtherDtoCollection $other_dto_collection;

    public string $string;

    public ?string $nullbable;

    public int $int;

    public bool $boolean;

    public Enum $enum;

    public RegularEnum $non_typescripted_type;

    public OtherDto $other_dto;

    /** @var array|\Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDto[] */
    public array $other_dto_array;

    public YetAnotherDto $another_namespace_dto;
}
