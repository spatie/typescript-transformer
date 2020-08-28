<?php

namespace Spatie\TypeScriptTransformer\Tests\Actions;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Spatie\TypeScriptTransformer\Actions\ResolveClassPropertyTypeAction;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Integration\Enum;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Test;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionProperty;
use Spatie\TypeScriptTransformer\Tests\Fakes\FakeReflectionType;

class ResolveClassPropertyTypeActionTest extends TestCase
{
    private ResolveClassPropertyTypeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ResolveClassPropertyTypeAction(
            new TypeResolver()
        );
    }

    /**
     * @test
     * @dataProvider docblockTypesProvider
     *
     * @param string $input
     * @param string $outputType
     */
    public function it_can_resolve_types_from_docblocks(string $input, string $outputType)
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()
                ->withDocComment("@var {$input}")
        );

        $this->assertEquals($outputType, (string) $type);
    }

    public function docblockTypesProvider(): array
    {
        return [
            ['int', 'int'],
            ['bool', 'bool'],
            ['string', 'string'],
            ['float', 'float'],
            ['array', 'array'],

            ['bool|int', 'bool|int'],
            ['?int', '?int'],
            ['int[]', 'int[]'],
        ];
    }

    /** @test */
    public function it_will_handle_no_docblock()
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()
        );

        $this->assertEquals('never', (string) $type);
    }

    /** @test */
    public function it_can_handle_another_non_var_docblock()
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()->withDocComment('@method bla')
        );

        $this->assertEquals('never', (string) $type);
    }

    /** @test */
    public function it_can_handle_an_incorrect_docblock()
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()->withDocComment('@var int  bool')
        );

        $this->assertEquals('int', (string) $type);
    }

    /**
     * @test
     * @dataProvider reflectionTypesProvider
     *
     * @param string $input
     * @param bool $isBuiltIn
     * @param string $outputType
     */
    public function it_can_resolve_reflection_types(string $input, bool $isBuiltIn, string $outputType)
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()
                ->withType(FakeReflectionType::create()->withIsBuiltIn($isBuiltIn)->withType($input))
        );

        $this->assertEquals($outputType, (string) $type);
    }

    public function reflectionTypesProvider(): array
    {
        return [
            ['int', true, 'int'],
            ['bool', true, 'bool'],
            ['string', true, 'string'],
            ['float', true, 'float'],
            ['array', true, 'array'],

            [Enum::class, false, '\\' . Enum::class],
        ];
    }

    /**
     * @test
     * @dataProvider ignoredTypesProvider
     *
     * @param string $reflection
     * @param string $docbloc
     * @param string $outputType
     */
    public function it_will_ignore_a_reflected_type_if_it_is_already_in_the_docblock(
        string $reflection,
        string $docbloc,
        string $outputType
    ) {
        $type = $this->action->execute(
            FakeReflectionProperty::create()
                ->withType(FakeReflectionType::create()->withType($reflection))
                ->withDocComment($docbloc)
        );

        $this->assertEquals($outputType, (string) $type);
    }

    public function ignoredTypesProvider(): array
    {
        return [
            ['int', 'int', 'int'],
            ['int|array', 'array', 'int|array'],
            ['int[]', 'array', 'int[]'],
            ['?int[]', 'array', '?int[]'],
        ];
    }

    /** @test */
    public function it_can_only_use_reflection_property_for_typing()
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()
                ->withType(FakeReflectionType::create()->withIsBuiltIn(true)->withType('string'))
        );

        $this->assertEquals('string', (string) $type);
    }

    /**
     * @test
     * @dataProvider nullifiedTypesProvider
     *
     * @param string $docbloc
     * @param string $outputType
     */
    public function it_can_nullify_types_based_upon_reflection(string $docbloc, string $outputType)
    {
        $type = $this->action->execute(
            FakeReflectionProperty::create()
                ->withType(FakeReflectionType::create()->withType('int')->withAllowsNull())
                ->withDocComment("@var {$docbloc}")
        );

        $this->assertEquals($outputType, (string) $type);
    }

    public function nullifiedTypesProvider(): array
    {
        return [
            ['', '?int'],
            ['int', '?int'],
            ['array|int', '?int|?array'],
        ];
    }
}
