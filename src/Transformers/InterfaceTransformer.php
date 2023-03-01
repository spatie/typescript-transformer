<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use Psalm\Type;
use ReflectionClass;
use ReflectionMethod;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TypeReferencesCollection;

class InterfaceTransformer extends DtoTransformer implements Transformer
{
    public function transform(ReflectionClass $class, string $name): ?TransformedType
    {
        if (! $class->isInterface()) {
            return null;
        }

        $transformedType = parent::transform($class, $name);
        $transformedType->keyword = 'interface';
        $transformedType->trailingSemicolon = false;

        return $transformedType;
    }

    protected function transformMethods(
        ReflectionClass $class,
        TypeReferencesCollection $typeReferences
    ): string {
        return array_reduce(
            $class->getMethods(ReflectionMethod::IS_PUBLIC),
            function (string $carry, ReflectionMethod $method) use ($typeReferences) {
                $transformedParameters = \array_reduce(
                    $method->getParameters(),
                    function (string $parameterCarry, \ReflectionParameter $parameter) use ($typeReferences) {
                        $type = $this->reflectionToTypeScript(
                            $parameter,
                            $typeReferences,
                            ...$this->typeProcessors()
                        );

                        $output = '';
                        if ($parameterCarry !== '') {
                            $output .= ', ';
                        }

                        return "{$parameterCarry}{$output}{$parameter->getName()}: {$type}";
                    },
                    ''
                );

                $returnType = 'any';
                if ($method->hasReturnType()) {
                    $returnType = $this->reflectionToTypeScript(
                        $method,
                        $typeReferences,
                        ...$this->typeProcessors()
                    );
                }

                return "{$carry}{$method->getName()}({$transformedParameters}): {$returnType};" . PHP_EOL;
            },
            ''
        );
    }

    protected function transformProperties(
        ReflectionClass $class,
        TypeReferencesCollection $typeReferences
    ): string {
        return '';
    }
}
