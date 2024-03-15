<?php

namespace Spatie\TypeScriptTransformer\Tests\Stubs;

use Illuminate\Support\Collection;
use stdClass;

class PhpDocTypesStub extends stdClass
{
    /** @var string */
    public $string;

    /** @var bool */
    public $bool;

    /** @var bool */
    public $boolean;

    /** @var int */
    public $int;

    /** @var int */
    public $integer;

    /** @var float */
    public $float;

    /** @var float */
    public $double;

    /** @var mixed */
    public $mixed;

    /** @var void */
    public $void;

    /** @var callable */
    public $callable;

    /** @var false */
    public $false;

    /** @var true */
    public $true;

    /** @var null */
    public $null;

    /** @var ?string */
    public $nullable;

    /** @var int|string */
    public $union;

    /** @var int&string */
    public $intersection;

    /** @var (int&string)|null */
    public $bnf;

    /** @var self */
    public $self;

    /** @var static */
    public $static;

    /** @var parent */
    public $parent;

    /** @var object */
    public $object;

    /** @var object{a: int, 'b': int, "c": int, d?: int} */
    public $objectShape;

    /** @var array */
    public $array;

    /** @var array<string> */
    public $arrayGeneric;

    /** @var array<int, string> */
    public $arrayGenericWithKey;

    /** @var array<array-key, string> */
    public $arrayGenericWithArrayKey;

    /** @var string[] */
    public $typeArray;

    /** @var array<int, array<string>> */
    public $nestedArray;

    /** @var array{a: int, 'b': int, "c": int, d?: int} */
    public $arrayShape;

    /** @var class-string */
    public $classString;

    /** @var class-string<StdClass> */
    public $classStringGeneric;

    /** @var \Illuminate\Support\Collection */
    public $reference;

    /** @var Collection */
    public $referenceWithImport;

    /** @var Collection<int, string> */
    public $generic;
}
