<?php

namespace Spatie\TypescriptTransformer\Tests\Transformers;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDtoCollection;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;

class DtoTransformerTest extends TestCase
{
    use MatchesSnapshots;

    private DtoTransformer $transformer;

    protected function setUp() : void
    {
        parent::setUp();

        $this->transformer = new DtoTransformer();
    }

    /** @test */
    public function it_will_replace_types()
    {
        $type = $this->transformer->transform(
            new ReflectionClass(Dto::class),
            'Typed'
        );

        $this->assertMatchesTextSnapshot($type->transformed);
        $this->assertEquals([
            Enum::class,
            RegularEnum::class,
            OtherDto::class,
            OtherDtoCollection::class,
            DtoWithChildren::class,
            YetAnotherDto::class,
        ], $type->missingSymbols->all());
    }
}
