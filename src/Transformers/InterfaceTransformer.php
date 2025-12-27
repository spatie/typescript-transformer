<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionMethod;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;
use Spatie\TypeScriptTransformer\Structures\TranspilationResult;

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
        MissingSymbolsCollection $missingSymbols
    ): TranspilationResult {
        return array_reduce(
            $class->getMethods(ReflectionMethod::IS_PUBLIC),
            function (TranspilationResult $carry, ReflectionMethod $method) use ($missingSymbols) {
                $transformedParameters = \array_reduce(
                    $method->getParameters(),
                    function (string $parameterCarry, \ReflectionParameter $parameter) use ($method, $missingSymbols) {
                        $type = $this->reflectionToTypeScript(
                            $parameter,
                            $missingSymbols,
                            false,
                            ...$this->typeProcessors()
                        );

                        $output = '';
                        if ($parameterCarry !== '') {
                            $output .= ', ';
                        }

                        return new TranspilationResult(
                            $type->dependencies,
                            "{$parameterCarry}{$output}{$parameter->getName()}: {$type}"
                        );
                    },
                    TranspilationResult::empty()
                );

                $returnType = 'any';
                if ($method->hasReturnType()) {
                    $returnType = $this->reflectionToTypeScript(
                        $method,
                        $missingSymbols,
                        false,
                        ...$this->typeProcessors()
                    );
                }

                return TranspilationResult::noDeps("{$carry}{$method->getName()}({$transformedParameters}): {$returnType};" . PHP_EOL);
            },
            TranspilationResult::empty()
        );
    }

    protected function transformProperties(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): TranspilationResult {
        return TranspilationResult::empty();
    }
}
