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
use Spatie\TypeScriptTransformer\Support\TransformationContext;
use Spatie\TypeScriptTransformer\Transformers\ClassPropertyProcessors\ClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNumber;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptRaw;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptUnknown;

it('can transform a class', function () {
    $class = new class () {
        public string $name;
    };

    $transformed = transformClass($class, new TransformationContext('ClassName', ['App', 'Data']));

    expect($transformed->getName())->toBe('ClassName');
    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier('ClassName'),
            new TypeScriptObject([
                new TypeScriptProperty(
                    new TypeScriptIdentifier('name'),
                    new TypeScriptString()
                ),
            ])
        )
    );
    expect($transformed->reference)->toEqual(
        new ReflectionClassReference(new ReflectionClass($class))
    );
    expect($transformed->location)->toEqual(['App', 'Data']);
    expect($transformed->export)->toBeTrue();
    expect($transformed->references)->toEqual([]);
});

it('can transform a class by depending on a TypeScriptTypeAttributeContract attribute type', function () {
    #[LiteralTypeScriptType('string')]
    class TestTypeScriptTypeAttributeContractForClass
    {
    }

    $transformed = transformClass(TestTypeScriptTypeAttributeContractForClass::class);

    expect($transformed->typeScriptNode)->toEqual(
        new TypeScriptAlias(
            new TypeScriptIdentifier(TestTypeScriptTypeAttributeContractForClass::class),
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

    expect(resolveObjectNode($class))->toEqual(
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

    expect(resolveObjectNode($class))->toEqual(
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

    expect(resolveObjectNode($class))->toEqual(
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

    expect(resolveObjectNode($class))->toEqual(
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

    expect(resolveObjectNode(TestClassPropertyAnnotation::class))->toEqual(
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

    expect(resolveObjectNode($class))->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString()
            ),
        ])
    );
});

it('can make a typescript property optional by annotation', function () {
    $class = new class () {
        #[Optional]
        public string $name;
    };

    expect(resolveObjectNode($class))->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString(),
                isOptional: true
            ),
        ])
    );
});

it('will type an untyped property as unknown', function () {
    $class = new class () {
        public $name;
    };

    expect(resolveObjectNode($class))->toEqual(
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

    expect(resolveObjectNode($class))->toEqual(
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
    $class = eval('$class = new readonly class {public string $name;};');

    expect(resolveObjectNode($class))->toEqual(
        new TypeScriptObject([
            new TypeScriptProperty(
                new TypeScriptIdentifier('name'),
                new TypeScriptString(),
                isReadonly: true
            ),
        ])
    );
})->skip(fn () => PHP_VERSION_ID < 80300);

it('can hide a property by adding a hidden attribute', function () {
    $class = new class () {
        #[Hidden]
        public string $property;
    };

    expect(resolveObjectNode($class))->toEqual(
        new TypeScriptObject([])
    );
});

it('can run a class property processor', function () {
    $class = new class () {
        public string $name;
    };

    $object = resolveObjectNode($class, transformer: new class () extends ClassTransformer {
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
                        $property->name = new TypeScriptIdentifier('newName');
                        $property->type = new TypeScriptNumber();
                        $property->isOptional = true;
                        $property->isReadonly = true;

                        return $property;
                    }
                },
            ];
        }
    });

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

    $object = resolveObjectNode($class, transformer: new class () extends ClassTransformer {
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
    });

    expect($object)->toEqual(
        new TypeScriptObject([])
    );
});
