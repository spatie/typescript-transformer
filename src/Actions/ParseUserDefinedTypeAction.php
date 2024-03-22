<?php

namespace Spatie\TypeScriptTransformer\Actions;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use Spatie\TypeScriptTransformer\Support\Concerns\Instanceable;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

class ParseUserDefinedTypeAction
{
    use Instanceable;

    protected TypeParser $typeParser;

    public function __construct(
        protected ConstExprParser $constExprParser = new ConstExprParser(),
        protected Lexer $lexer = new Lexer(),
        protected TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptNodeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
    ) {
        $this->typeParser = new TypeParser($constExprParser);
    }

    public function execute(string $type, ?ReflectionClass $reflectionClass = null): TypeScriptNode
    {
        return $this->transpilePhpStanTypeToTypeScriptNodeAction->execute(
            $this->typeParser->parse(new TokenIterator($this->lexer->tokenize($type))),
            $reflectionClass,
        );
    }
}
