<?php

namespace Spatie\TypescriptTransformer\Tests\FakeClasses\Dto;

use Spatie\DataTransferObject\DataTransferObject;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\RegularEnum;

class TypeDto extends DataTransferObject
{
    public RegularEnum $other_type;

    public string $string;

    public ?string $nullable_string;

    public string $default_string = 'default';

    public int $int;

    public bool $bool;

    public float $float;

    public object $object;

    /** @var string */
    public $documented_string;

    /** @var int|string */
    public $mixed;

    /** @var int[] */
    public $documented_array;

    /** @var int|string|int[]|string[] */
    public $mixed_with_array;

    /** @var int[]|null[] */
    public $array_with_null;

    public array $array;

    public $none;
}
