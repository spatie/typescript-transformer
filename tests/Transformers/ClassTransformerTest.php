<?php

namespace Spatie\TypeScriptTransformer\Tests\Transformers;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionClass;
use ReflectionProperty;
use Spatie\TypeScriptTransformer\Attributes\Hidden;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\References\ReflectionClassReference;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\ReadonlyClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;
use Spatie\TypeScriptTransformer\Tests\Support\AllClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnknown;

it('can transform a class', function () {
    $transformed = transformSingle(SimpleClass::class);

    expect($transformed->getName())->toBe('SimpleClass');
    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier('SimpleClass'),
            new TypeScriptObject([
                new TypeScriptProperty(
                    new TypeScriptIdentifier('stringProperty'),
                    new TypeScriptString()
                ),
                new TypeScriptProperty(
                    new TypeScriptIdentifier('constructorPromotedStringProperty'),
                    new TypeScriptString()
                ),
            ])
        )
    );
    expect($transformed->reference)->toEqual(
        new ReflectionClassReference(new ReflectionClass(SimpleClass::class))
    );
    expect($transformed->location)->toEqual(['Spatie', 'TypeScriptTransformer', 'Tests', 'Fakes', 'TypesToProvide']);
    expect($transformed->export)->toBeTrue();
    expect($transformed->references)->toEqual([]);
});

it('can transform a class by depending on a TypeScriptTypeAttributeContract attribute type', function () {
    #[LiteralTypeScriptType('string')]
    class TestTypeScriptTypeAttributeContractForClass
    {
    }

    $transformed = transformSingle(TestTypeScriptTypeAttributeContractForClass::class);

    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier('TestTypeScriptTypeAttributeContractForClass'),
            new TypeScriptRaw('string'),
        )
    );
});

it('transforms only public non static properties by default', function () {
    $class = new class () {
        public string $public;

        protected string $protected;

        private string $private;

        public static string $publicStatic;

        protected static string $protectedStatic;

        private static string $privateStatic;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('public'),
                new TypeScriptString()
            ),
        ])
    );
});


it('can type a property using php reflection types', function () {
    $class = new class () {
        public string $name;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString()
            ),
        ])
    );
});

it('can type a property using a var annotation', function () {
    $class = new class () {
        /** @var string */
        public $name;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString()
            ),
        ])
    );
});


it('can type a property using a constructor annotation', function () {
    $class = new class ('') {
        /**
         * @param string $name
         */
        public function __construct(
            public $name,
        ) {
        }
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString()
            ),
        ])
    );
});

it('can type a property using a class property annotation', function () {
    /**
     * @property string $name
     */
    class TestClassPropertyAnnotation
    {
        public $name;
    }

    expect(transformSingle(TestClassPropertyAnnotation::class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString()
            ),
        ])
    );
});

it('can type a property using a TypeScriptTypeAttributeContract attribute type', function () {
    $class = new class () {
        #[TypeScriptType('string')]
        public $name;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString()
            ),
        ])
    );
});

it('can make a typescript property optional by attribute', function () {
    $class = new class () {
        #[Optional]
        public string $name;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString(),
                isOptional: true
            ),
        ])
    );
});

it('can make a complete class optional by attribute', function () {
    #[Optional]
    class TestAllPropertiesOptionalByClassAttribute
    {
        public string $name;
        public int $age;
    }

    expect(transformSingle(TestAllPropertiesOptionalByClassAttribute::class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString(),
                isOptional: true
            ),
            new TypeScriptProperty(
                new TypeScriptIdentifier('age'),
                new TypeScriptNumber(),
                isOptional: true
            ),
        ])
    );
});

it('will type an untyped property as unknown', function () {
    $class = new class () {
        public $name;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptUnknown()
            ),
        ])
    );
});

it('can make a TypeScript property readonly by adding the modifier to the property', function () {
    $class = new class () {
        public readonly string $name;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString(),
                isReadonly: true
            ),
        ])
    );
});

it('can make a TypeScript property readonly by adding the modifier to the class', function () {
    expect(transformSingle(ReadonlyClass::class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('property'),
                new TypeScriptString(),
                isReadonly: true
            ),
        ])
    );
});

it('can hide a property by adding a hidden attribute', function () {
    $class = new class () {
        #[Hidden]
        public string $property;
    };

    expect(transformSingle($class)->typeScriptNode->type)->toEqual(
        new TypeScriptObject([])
    );
});

it('can run a class property processor', function () {
    $class = new class () {
        public string $name;
    };

    $object = transformSingle($class, transformer: new class () extends AllClassTransformer {
        protected function classPropertyProcessors(): array
        {
            return [
                new class () implements ClassPropertyProcessor {
                    public function execute(ReflectionProperty $reflection, ?TypeNode $annotation, TypeScriptProperty $property): ?TypeScriptProperty
                    {
                        $property->name = new TypeScriptIdentifier('newName');
                        $property->type = new TypeScriptNumber();
                        $property->isOptional = true;
                        $property->isReadonly = true;

                        return $property;
                    }
                },
            ];
        }
    })->typeScriptNode->type;

    expect($object)->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('newName'),
                new TypeScriptNumber(),
                isOptional: true,
                isReadonly: true
            ),
        ])
    );
});

it('can use a class property processor to remove a property', function () {
    $class = new class () {
        public string $name;
    };

    $object = transformSingle($class, transformer: new class () extends ClassTransformer {
        protected function shouldTransform(ReflectionClass $reflection): bool
        {
            return true;
        }

        protected function classPropertyProcessors(): array
        {
            return [
                new class () implements ClassPropertyProcessor {
                    public function execute(ReflectionProperty $reflection, ?TypeNode $annotation, TypeScriptProperty $property): ?TypeScriptProperty
                    {
                        return null;
                    }
                },
            ];
        }
    })->typeScriptNode->type;

    expect($object)->toEqual(
        new TypeScriptObject([])
    );
});
