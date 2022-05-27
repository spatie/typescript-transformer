<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeInterface;
use Spatie\TypeScriptTransformer\Transformers\InterfaceTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class InterfaceTransformerTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_will_only_convert_interfaces(): void
    {
        $transformer = new InterfaceTransformer(
            TypeScriptTransformerConfig::create()
        );

        $this->assertNotNull($transformer->transform(
            new ReflectionClass(DateTimeInterface::class),
            'State',
        ));

        $this->assertNull($transformer->transform(
            new ReflectionClass(DateTime::class),
            'State',
        ));
    }

    /** @test */
    public function it_will_replace_methods(): void
    {
        $transformer = new InterfaceTransformer(
            TypeScriptTransformerConfig::create()
        );

        $type = $transformer->transform(
            new ReflectionClass(FakeInterface::class),
            'State',
        );

        $this->assertMatchesTextSnapshot($type->transformed);
    }
}
