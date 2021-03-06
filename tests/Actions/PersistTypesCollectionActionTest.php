<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Actions\PersistTypesCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class PersistTypesCollectionActionTest extends TestCase
{
    use MatchesSnapshots;

    private PersistTypesCollectionAction $action;

    private TemporaryDirectory $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = (new TemporaryDirectory())->create();

        $this->action = new PersistTypesCollectionAction(
            TypeScriptTransformerConfig::create()
                ->autoDiscoverTypes(__DIR__ . '/../FakeClasses')
                ->transformers([MyclabsEnumTransformer::class])
                ->outputFile($this->temporaryDirectory->path('types.d.ts'))
        );
    }

    /** @test */
    public function it_will_persist_the_types()
    {
        $collection = TypesCollection::create();

        $collection[] = FakeTransformedType::fake('Enum')->withoutNamespace();
        $collection[] = FakeTransformedType::fake('Enum')->withNamespace('test');
        $collection[] = FakeTransformedType::fake('Enum')->withNamespace('test\test');

        $this->action->execute($collection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
    }

    /** @test */
    public function it_can_persist_multiple_types_in_one_namespace()
    {
        $collection = TypesCollection::create();

        $collection[] = FakeTransformedType::fake('Enum')->withTransformed('transformed Enum')->withoutNamespace();
        $collection[] = FakeTransformedType::fake('OtherEnum')->withTransformed('transformed OtherEnum')->withoutNamespace();
        $collection[] = FakeTransformedType::fake('Enum')->withTransformed('transformed test\Enum')->withNamespace('test');
        $collection[] = FakeTransformedType::fake('OtherEnum')->withTransformed('transformed test\OtherEnum')->withNamespace('test');

        $this->action->execute($collection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
    }

    /** @test */
    public function it_can_re_save_the_file()
    {
        $collection = TypesCollection::create();

        $collection[] = FakeTransformedType::fake('Enum')->withoutNamespace();

        $this->action->execute($collection);

        $collection[] = FakeTransformedType::fake('Enum')->withNamespace('test');

        $this->action->execute($collection);

        $this->assertMatchesFileSnapshot($this->temporaryDirectory->path("types.d.ts"));
    }
}
