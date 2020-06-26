<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\Actions\ResolvePropertyTypesAction;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Enum;

class ResolvePropertyTypesActionTest extends TestCase
{
    private ResolvePropertyTypesAction $action;

    private MissingSymbolsCollection $missingSymbols;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missingSymbols = new MissingSymbolsCollection();

        $this->action = new ResolvePropertyTypesAction($this->missingSymbols);
    }

    /**
     * @test
     * @dataProvider typesDataProvider
     */
    public function it_can_resolve_types(
        array $allowed,
        array $arrayAllowed,
        bool $nullable,
        array $expected
    ) {
        $types = $this->action->execute($allowed, $arrayAllowed, $nullable);

        $this->assertEquals($expected, $types);
    }

    public function typesDataProvider(): array
    {
        return [
            // Simple
            [['string'], [], false, ['string']],
            [['integer'], [], false, ['number']],
            [['boolean'], [], false, ['boolean']],
            [['double'], [], false, ['number']],
            [['null'], [], false, ['null']],
            [['object'], [], false, ['object']],
            [['array'], [], false, ['Array<any>']],

            // Objects
            [[Enum::class], [], false, ['{%'.Enum::class.'%}']],
            [[], [Enum::class], false, ['Array<{%'.Enum::class.'%}>']],

            // Arrays
            [[], ['string'], false, ['Array<string>']],
            [['string[]'], ['string'], false, ['Array<string>']],
            [['array'], ['string'], false, ['Array<string>']],
            [[], ['string', 'integer'], false, ['Array<string | number>']],

            // Mixed
            [['string', 'integer', Enum::class], [], false, ['string', 'number', '{%'.Enum::class.'%}']],
            [['string', 'integer', Enum::class], [], true, ['string', 'number', '{%'.Enum::class.'%}', 'null']],
            [[], ['string', 'integer', Enum::class], false, ['Array<string | number | {%'.Enum::class.'%}>']],

            // Nullable
            [['string', 'null'], [], false, ['string', 'null']],
            [['string', 'null'], [], true, ['string', 'null']],
            [['string'], [], true, ['string', 'null']],
            [[], ['string'], true, ['Array<string>', 'null']],
            [[], ['string', 'null'], false, ['Array<string | null>']],

            // Empty
            [[], [], false, ['any']],
            [[], [], true, ['any']],
        ];
    }
}
