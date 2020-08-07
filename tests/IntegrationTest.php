<?php

namespace Spatie\TypescriptTransformer\Tests;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\BetterReflection;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypescriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Test;
use Spatie\TypescriptTransformer\Transformers\DtoCollectionTransformer;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypescriptTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class IntegrationTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_works()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $transformer = new TypescriptTransformer(
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/FakeClasses/Integration')
                ->transformers([
                    MyclabsEnumTransformer::class,
                    DtoTransformer::class,
                ])
                ->collectors([
                    AnnotationCollector::class,
                ])
                ->outputFile($temporaryDirectory->path('types.d.ts'))
        );

        $transformer->transform();

        $transformed = file_get_contents($temporaryDirectory->path('types.d.ts'));

        $this->assertMatchesSnapshot($transformed);
    }
}
