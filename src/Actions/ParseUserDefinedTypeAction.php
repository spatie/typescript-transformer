<?php

namespace Spatie\TypeScriptTransformer\Actions;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Support\Concerns\Instanceable;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

class ParseUserDefinedTypeAction
{
    use Instanceable;

    protected TypeParser $typeParser;

    public function __construct(
        protected ParserConfig $parserConfig = new ParserConfig(usedAttributes: []),
        protected ConstExprParser $constExprParser = new ConstExprParser(new ParserConfig(usedAttributes: [])),
        protected Lexer $lexer = new Lexer(new ParserConfig(usedAttributes: [])),
        protected TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptNodeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
    ) {
        $this->typeParser = new TypeParser($this->parserConfig, $constExprParser);
    }

    public function execute(string $type, ?PhpClassNode $node = null): TypeScriptNode
    {
        return $this->transpilePhpStanTypeToTypeScriptNodeAction->execute(
            $this->typeParser->parse(new TokenIterator($this->lexer->tokenize($type))),
            $node,
        );
    }
}
