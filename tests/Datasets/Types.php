<?php

use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;

dataset('types', [
    // Compound
    ['string|integer|' . Enum::class, 'string | number | {%' . Enum::class . '%}'],
    ['string|integer|null|' . Enum::class, 'string | number | null | {%' . Enum::class . '%}'],
    ['(string|integer|null|' . Enum::class . ')[]', 'Array<string | number | null | {%' . Enum::class . '%}>'],

    // Arrays
    ['string[]', 'Array<string>'],
    ['string[]|Array<String>', 'Array<string>'],
    ['(string|integer)[]', 'Array<string | number>'],
    ['Array<string|integer>', 'Array<string | number>'],

    // Objects
    ['Array<int, string>', '{ [key: number]: string }'],
    ['Array<string, int>', '{ [key: string]: number }'],
    ['Array<string, int|bool>', '{ [key: string]: number | boolean }'],

    // Null
    ['?string', 'string | null'],
    ['?string[]', 'Array<string> | null'],

    // Objects
    [Enum::class, '{%' . Enum::class . '%}'],
    [Enum::class . '[]', 'Array<{%' . Enum::class . '%}>'],

    // Simple
    ['string', 'string'],
    ['boolean', 'boolean'],
    ['integer', 'number'],
    ['double', 'number'],
    ['float', 'number'],
    ['class-string<' . Enum::class . '>', 'string'],
    ['null', 'null'],
    ['object', 'object'],
    ['array', 'Array<any>'],

    // references
    ['self', '{%fake_class%}'],
    ['static', '{%fake_class%}'],
    ['$this', '{%fake_class%}'],

    // Scalar
    ['scalar', 'string|number|boolean'],

    // Mixed
    ['mixed', 'any'],

    // Collections
    ['Collection<int>', 'Array<number>'],
]);
