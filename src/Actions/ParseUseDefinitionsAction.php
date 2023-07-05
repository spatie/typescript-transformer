<?php

namespace Spatie\TypeScriptTransformer\Actions;

use Illuminate\Support\Collection;
use ParseError;
use PhpToken;
use Spatie\StructureDiscoverer\Collections\TokenCollection;
use Spatie\StructureDiscoverer\Collections\UsageCollection;
use Spatie\StructureDiscoverer\TokenParsers\UseTokenParser;

class ParseUseDefinitionsAction
{
    public function __construct(
        protected UseTokenParser $useResolver = new UseTokenParser(),
    ) {
    }

    public function execute(
        string $filename,
    ): UsageCollection {
        /** @todo refactor this to the structure discoverer package, it is copied from there */
        try {
            $contents = file_get_contents($filename);

            /** @var TokenCollection $tokens */
            $tokens = collect(PhpToken::tokenize($contents, TOKEN_PARSE))
                ->reject(fn (PhpToken $token) => $token->is([T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]))
                ->values()
                ->pipe(fn (Collection $collection): TokenCollection => new TokenCollection($collection->all()));
        } catch (ParseError) {
            return new UsageCollection();
        }

        $usages = new UsageCollection();

        for ($index = 0; $index < $tokens->count(); $index++) {
            if ($tokens->get($index)->is(T_USE)) {
                $usages->add(...$this->useResolver->execute($index + 1, $tokens));
            }
        }

        return $usages;
    }
}
