<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DtoTransformerTest extends TestCase
{
    use MatchesSnapshots;

    private DtoTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $config = TypeScriptTransformerConfig::create()
            ->defaultTypeReplacements([
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
            protected function typeProcessors(): array
            {
                $onlyStringPropertiesProcessor = new class implements TypeProcessor {
                    public function process(Type $type, ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection): ?Type
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
