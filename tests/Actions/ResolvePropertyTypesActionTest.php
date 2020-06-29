<?php

namespace Spatie\TypescriptTransformer\Tests\Actions;

use PHPUnit\Framework\TestCase;
use Spatie\TypescriptTransformer\Actions\ResolvePropertyTypesAction;
use Spatie\TypescriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDto;
use Spatie\TypescriptTransformer\Tests\FakeClasses\Integration\OtherDtoCollection;

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
            [['array'], [], false, ['Array<never>']],

            // Objects
            [[Enum::class], [], false, ['{%'.Enum::class.'%}']],
            [[], [Enum::class], false, ['Array<{%'.Enum::class.'%}>']],

            // DTO
            [[OtherDto::class], [], false, ['{%'.OtherDto::class.'%}']],
            [[OtherDto::class], [], true, ['{%'.OtherDto::class.'%}', 'null']],

            [[OtherDtoCollection::class], [], false, ['{%'.OtherDtoCollection::class.'%}']],
            [[OtherDtoCollection::class], ['string'], false, ['{%'.OtherDtoCollection::class.'%}', 'Array<string>']], // This can be better

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
            [[], [], false, ['never']],
            [[], [], true, ['never']],
        ];
    }
}
