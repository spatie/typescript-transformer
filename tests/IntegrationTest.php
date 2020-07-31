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
                    DtoCollectionTransformer::class,
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

    /** @test */
    public function testje()
    {
        $classInfo = (new BetterReflection())->classReflector()->reflect(Test::class);
        $methodInfo = $classInfo->getProperty('property');


        dd($methodInfo->getDocBlockTypes());

        // Will fetch the language hint
        var_dump($methodInfo->getType());

        // Will fetch an array of Type objects for the typehint in the DocBlock
//        var_dump($parameterInfo->getDocBlockTypes());

// Will fetch an array of strings describing the DocBlock type hints
//        var_dump($parameterInfo->getDocBlockTypeStrings());
    }
}
