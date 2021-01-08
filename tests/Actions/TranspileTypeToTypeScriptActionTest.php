<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Actions\TranspileTypeToTypeScriptAction;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;

class TranspileTypeToTypeScriptActionTest extends TestCase
{
    private TranspileTypeToTypeScriptAction $action;

    private MissingSymbolsCollection $missingSymbols;

    private TypeResolver $typeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missingSymbols = new MissingSymbolsCollection();

        $this->typeResolver = new TypeResolver();

        $this->action = new TranspileTypeToTypeScriptAction(
            $this->missingSymbols,
            'fake_class'
        );
    }

    /**
     * @test
     * @dataProvider typesDataProvider
     *
     * @param string $input
     * @param string $output
     */
    public function it_can_resolve_types(
        string $input,
        string $output
    ) {
        $resolved = $this->action->execute(
            $this->typeResolver->resolve($input),
        );

        $this->assertEquals($output, $resolved);
    }

    public function typesDataProvider(): array
    {
        return [
            // Compound
            ['string|integer|' . Enum::class, 'string | number | {%' . Enum::class . '%}'],
            ['string|integer|null|' . Enum::class, 'string | number | null | {%' . Enum::class . '%}'],
            ['(string|integer|null|' . Enum::class . ')[]', 'Array<string | number | null | {%' . Enum::class . '%}>'],

            // Arrays
            ['string[]', 'Array<string>'],
            ['string[]|Array<String>', 'Array<string>'],
            ['(string|integer)[]', 'Array<string | number>'],
            ['Array<string|integer>', 'Array<string | number>'],

            // Objects
            ['Array<int, string>', '{ [key: number]: string }'],
            ['Array<string, int>', '{ [key: string]: number }'],
            ['Array<string, int|bool>', '{ [key: string]: number | boolean }'],

            // Null
            ['?string', 'string | null'],
            ['?string[]', 'Array<string | null>'],

            // Objects
            [Enum::class, '{%' . Enum::class . '%}'],
            [Enum::class . '[]', 'Array<{%' . Enum::class . '%}>'],

            // Simple
            ['string', 'string'],
            ['boolean', 'boolean'],
            ['integer', 'number'],
            ['double', 'number'],
            ['float', 'number'],
            ['class-string<' . Enum::class . '>', 'string'],
            ['null', 'null'],
            ['object', 'object'],
            ['array', 'Array<any>'],

            // references
            ['self', '{%fake_class%}'],
            ['static', '{%fake_class%}'],
            ['$this', '{%fake_class%}'],

            // Scalar
            ['scalar', 'string|number|boolean'],

            // Mixed
            ['mixed', 'any'],
        ];
    }

    /** @test */
    public function it_can_resolve_self_referencing_types_without_current_class()
    {
        $action = new TranspileTypeToTypeScriptAction($this->missingSymbols);

        $this->assertEquals('any', $action->execute(new Self_()));
        $this->assertEquals('any', $action->execute(new Static_()));
        $this->assertEquals('any', $action->execute(new This()));
    }
}
