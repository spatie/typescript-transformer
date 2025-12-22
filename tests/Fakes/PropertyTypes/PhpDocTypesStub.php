<?php

namespace Spatie\TypeScriptTransformer\Tests\Fakes\PropertyTypes;

use Illuminate\Support\Collection;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\StringBackedEnum;
use stdClass;

class PhpDocTypesStub extends stdClass
{
    public const ARRAYCONST = [
        'script' => 2,
        'type' => 1,
    ];

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

    /** @var positive-int */
    public $positiveInt;

    /** @var negative-int */
    public $negativeInt;

    /** @var non-positive-int */
    public $nonPositiveInt;

    /** @var non-negative-int */
    public $nonNegativeInt;

    /** @var non-zero-int */
    public $nonZeroInt;

    /** @var int<0, 100> */
    public $intRange;

    /** @var int<min, 100> */
    public $intRangeMin;

    /** @var int<0, max> */
    public $intRangeMax;

    /** @var numeric */
    public $numeric;

    /** @var float */
    public $float;

    /** @var float */
    public $double;

    /** @var mixed */
    public $mixed;

    /** @var scalar */
    public $scalar;

    /** @var array-key */
    public $arrayKey;

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
    public $arrayGenericWithIntKey;

    /** @var array<string, string> */
    public $arrayGenericWithStringKey;

    /** @var array<array-key, string> */
    public $arrayGenericWithArrayKey;

    /** @var non-empty-array<string> */
    public $nonEmptyArrayGeneric;

    /** @var non-empty-array<string, string> */
    public $nonEmptyArrayGenericWithKey;

    /** @var array<int | string> */
    public $unionTypeArray;

    /** @var list<string> */
    public $list;

    /** @var non-empty-list<string> */
    public $nonEmptyList;

    /** @var string[] */
    public $typeArray;

    /** @var array<string, array<string>> */
    public $nestedArray;

    /** @var array{a: int, 'b': int, "c": int, d?: int} */
    public $arrayShape;

    /** @var class-string */
    public $classString;

    /** @var class-string<StdClass> */
    public $classStringGeneric;

    /** @var interface-string */
    public $interfaceString;

    /** @var interface-string<self> */
    public $interfaceStringGeneric;

    /** @var trait-string */
    public $traitString;

    /** @var trait-string<self> */
    public $traitStringGeneric;

    /** @var callable-string */
    public $callableString;

    /** @var callable-string<self> */
    public $callableStringGeneric;

    /** @var enum-string */
    public $enumString;

    /** @var enum-string<StringBackedEnum> */
    public $enumStringGeneric;

    /** @var lowercase-string */
    public $lowercaseString;

    /** @var uppercase-string */
    public $uppercaseString;

    /** @var literal-string */
    public $literalString;

    /** @var numeric-string */
    public $numericString;

    /** @var non-empty-string */
    public $nonEmptyString;

    /** @var non-empty-lowercase-string */
    public $nonEmptyLowercaseString;

    /** @var non-empty-uppercase-string */
    public $nonEmptyUppercaseString;

    /** @var truthy-string */
    public $truthyString;

    /** @var non-falsy-string */
    public $nonFalsyString;

    /** @var non-empty-literal-string */
    public $nonEmptyLiteralString;

    /** @var \Illuminate\Support\Collection */
    public $reference;

    /** @var Collection */
    public $referenceWithImport;

    /** @var Collection<int, string> */
    public $generic;

    /** @var key-of<self::ARRAYCONST> */
    public $keyOfArrayConst;

    /** @var value-of<PhpDocTypesStub::ARRAYCONST> */
    public $valueOfArrayConst;

    /** @var key-of<StringBackedEnum> */
    public $keyOfEnum;

    /** @var value-of<StringBackedEnum> */
    public $valueOfEnum;
}
