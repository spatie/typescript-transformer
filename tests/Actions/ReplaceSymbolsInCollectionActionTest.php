<?php


namespace Spatie\TypeScriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Actions\ReplaceSymbolsInCollectionAction;
use Spatie\TypeScriptTransformer\Structures\TypesCollection;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeTransformedType;

class ReplaceSymbolsInCollectionActionTest extends TestCase
{
    /** @test */
    public function it_can_replace_missing_symbols()
    {
        $action = new ReplaceSymbolsInCollectionAction();

        $collection = TypesCollection::create();

        $collection[] = FakeTransformedType::fake('Enum')->withNamespace('enums');
        $collection[] = FakeTransformedType::fake('Dto')
            ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
            ->withMissingSymbols([
                'enum' => 'enums\Enum',
                'non-existing' => 'non-existing',
            ]);

        $collection = $action->execute($collection);

        $this->assertEquals('{enum: enums.Enum, non-existing: any}', $collection['Dto']->transformed);
    }

    /** @test */
    public function it_can_replace_missing_symbols_without_fully_qualified_names()
    {
        $action = new ReplaceSymbolsInCollectionAction();

        $collection = TypesCollection::create();

        $collection[] = FakeTransformedType::fake('Enum')->withNamespace('enums');
        $collection[] = FakeTransformedType::fake('Dto')
            ->withTransformed('{enum: {%enums\Enum%}, non-existing: {%non-existing%}}')
            ->withMissingSymbols([
                'enum' => 'enums\Enum',
                'non-existing' => 'non-existing',
            ]);

        $collection = $action->execute($collection, false);

        $this->assertEquals('{enum: Enum, non-existing: any}', $collection['Dto']->transformed);
    }
}
