<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses\LaravelDto;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDtoCollection;

class LaravelDto
{
    /** @var \Spatie\TypeScriptTransformer\Tests\FakeClasses\LaravelDto\LaravelOtherDto[] */
    public array $other_dto_array;

    public Collection $non_typed_laravel_collection;

    /** @var \Illuminate\Support\Collection|\Spatie\TypeScriptTransformer\Tests\FakeClasses\LaravelDto\LaravelOtherDto[] */
    public Collection $other_dto_laravel_collection;

    /** @var \Illuminate\Database\Eloquent\Collection|\Spatie\TypeScriptTransformer\Tests\FakeClasses\LaravelDto\LaravelOtherDto[] */
    public EloquentCollection $other_dto_laravel_eloquent_collection;
}
