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
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Enum\RegularEnum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Dto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\DtoWithChildren;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\LevelUp\YetAnotherDto;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;
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
    public function a_type_processor_can_remove_properties()
    {
        $config = TypeScriptTransformerConfig::create();

        $transformer = new class($config) extends DtoTransformer {
            protected function typeProcessors(): array
            {
                $onlyStringPropertiesProcessor = new class implements TypeProcessor {
                    public function process(
                        Type $type,
                        ReflectionProperty|ReflectionParameter|ReflectionMethod $reflection,
                        MissingSymbolsCollection $missingSymbolsCollection
                    ): ?Type
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

    /** @test */
    public function it_will_take_transform_as_typescript_attributes_into_account()
    {
        $class = new class {
            #[TypeScriptType('int')]
            public $int;

            #[TypeScriptType('int|bool')]
            public int $overwritable;

            #[TypeScriptType(['an_int' => 'int', 'a_bool' => 'bool'])]
            public $object;

            #[LiteralTypeScriptType('never')]
            public $pure_typescript;

            #[LiteralTypeScriptType(['an_any' => 'any', 'a_never' => 'never'])]
            public $pure_typescript_object;

            public int $regular_type;
        };

        $type = $this->transformer->transform(
            new ReflectionClass($class),
            'Typed'
        );

        $this->assertMatchesSnapshot($type->transformed);
    }
}
