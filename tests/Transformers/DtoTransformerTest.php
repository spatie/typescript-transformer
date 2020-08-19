<?php

namespace Spatie\TypescriptTransformer\Tests\Transformers;

use DateTime;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypescriptTransformer\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class DtoTransformerTest extends TestCase
{
    use MatchesSnapshots;

    private DtoTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $config = TypeScriptTransformerConfig::create()
            ->classPropertyReplacements([
                DateTime::class => 'string',
            ]);

        $this->transformer = new DtoTransformer($config);
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
            DtoWithChildren::class,
            YetAnotherDto::class,
        ], $type->missingSymbols->all());
    }

    /** @test */
    public function a_property_processor_can_remove_properties()
    {
        $config = TypeScriptTransformerConfig::create();

        $transformer = new class($config) extends DtoTransformer {
            protected function getClassPropertyProcessors(): array
            {
                $onlyStringPropertiesProcessor = new class implements ClassPropertyProcessor {
                    public function process(Type $type, ReflectionProperty $reflection): ?Type
                    {
                        return $type instanceof String_ ? $type : null;
                    }
                };

                return [$onlyStringPropertiesProcessor];
            }
        };

        $type = $transformer->transform(
            new ReflectionClass(Dto::class),
            'Typed'
        );

        $this->assertMatchesTextSnapshot($type->transformed);
    }
}
