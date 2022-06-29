<?php

namespace Spatie\TypeScriptTransformer\Transformers;

use ReflectionClass;
use ReflectionMethod;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

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
    ): string {
        return array_reduce(
            $class->getMethods(ReflectionMethod::IS_PUBLIC),
            function (string $carry, ReflectionMethod $method) use ($missingSymbols) {
                $transformedParameters = \array_reduce(
                    $method->getParameters(),
                    function (string $parameterCarry, \ReflectionParameter $parameter) use ($missingSymbols) {
                        $type = $this->reflectionToTypeScript(
                            $parameter,
                            $missingSymbols,
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
                        $missingSymbols,
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
        MissingSymbolsCollection $missingSymbols
    ): string {
        return '';
    }
}
