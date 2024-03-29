<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration;

use DateTime;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;

/** @typescript */
class Dto extends DataTransferObject
{
    public string $string;

    public ?string $nullbable;

    public string $default = 'default';

    public int $int;

    public bool $boolean;

    public float $float;

    public object $object;

    public array $array;

    public $none;

    /** @var string */
    public $documented_string;

    /** @var int|string */
    public $mixed;

    /** @var int|float */
    public $number;

    /** @var int[] */
    public $documented_array;

    /** @var int|string|array<int|string> */
    public $mixed_with_array;

    /** @var array<int|null> */
    public $array_with_null;

    public Enum $enum;

    public RegularEnum $non_typescripted_type;

    public OtherDto $other_dto;

    /** @var \Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto[] */
    public array $other_dto_array;

    public OtherDtoCollection $other_dto_collection;

    public DtoWithChildren $dto_with_children;

    public YetAnotherDto $another_namespace_dto;

    /** @var string|int */
    public ?string $nullable_string;

    public DateTime $reflection_replaced_default_type;

    /** @var \DateTime */
    public $docblock_replaced_default_type;

    /** @var \DateTime[] */
    public array $array_replaced_default_type;

    /** @var array<string,mixed> */
    public array $array_as_object;
}
