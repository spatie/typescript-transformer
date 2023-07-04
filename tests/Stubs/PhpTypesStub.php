<?php

namespace Spatie\TypeScriptTransformer\Tests\Stubs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use stdClass;

class PhpTypesStub extends stdClass
{
    public string $string;

    public bool $bool;

    public int $int;

    public float $float;

    public mixed $mixed;

    public false $false;

    public true $true;

    public null $null;

    public ?string $nullable;

    public int|string $union;

    public Collection&Arrayable $intersection;

    public (Collection&Arrayable)|null $bnf;

    public self $self;

    public static $static;

    public parent $parent;

    public object $object;

    public array $array;

    public Collection $reference;
}
