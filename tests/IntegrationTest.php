<?php

namespace Spatie\TypescriptTransformer\Tests;

use PHPUnit\Framework\TestCase;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypescriptTransformer\Transformers\DtoCollectionTransformer;
use Spatie\TypescriptTransformer\Transformers\DtoTransformer;
use Spatie\TypescriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypescriptTransformer\TypescriptTransformer;
use Spatie\TypescriptTransformer\TypeScriptTransformerConfig;

class IntegrationTest extends TestCase
{
    /** @test */
    public function it_works()
    {
        $this->markTestIncomplete();

        $temporaryDirectory = (new TemporaryDirectory())->create();

        $transformer = new TypescriptTransformer(
            TypeScriptTransformerConfig::create()
                ->searchingPath(__DIR__ . '/FakeClasses/Integration')
                ->transformers([
                    MyclabsEnumTransformer::class,
                    DtoTransformer::class,
                    DtoCollectionTransformer::class,
                ])
                ->outputFile($temporaryDirectory->path('types.d.ts'))
        );

        $transformer->transform();

        dd(file_get_contents($temporaryDirectory->path('types.d.ts')));
    }
}
