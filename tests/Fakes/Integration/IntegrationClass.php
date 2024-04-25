<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\Integration;

use DateTime;
use Exception;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Tests\Fakes\Integration\Level\LevelUpClass;

class IntegrationClass
{
    public string $string;

    public ?string $nullable;

    public string $default = 'default';

    public int $int;

    public bool $boolean;

    public float $float;

    public object $object;

    public array $array;

    public mixed $mixed;

    public $none;

    /** @var string */
    public $var_annotated;

    /** @var int|string */
    public $union;

    /** @var int[] */
    public $annotated_array;

    /** @var array{int: int, string: string, level_up: LevelUpClass} */
    public array $complex_annotated_array;

    /** @var int|string|array<int|string> */
    public $complex_union;

    public Enum $enum;

    public Exception $non_typescript_type;

    /** @var IntegrationItem[] */
    public array $array_of_reference;

    public DateTime $replacement_type;

    /** @var \DateTime */
    public $annotated_replacement_type;

    /** @var \DateTime[] */
    public array $array_annotated_replacement_type;

    public LevelUpClass $level_up_class;

    #[Hidden]
    public string $hidden;

    public readonly string $readonly;

    #[Optional]
    public string $optional;

    /**
     * @param array<int> $constructor_annotated_array
     */
    public function __construct(
        public array $constructor_annotated_array,
        /** @var array<LevelUpClass> */
        public array $constructor_inline_annotated_array,
    ) {
    }
}
