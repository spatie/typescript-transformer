<?php

namespace Spatie\TypeScriptTransformer\Actions;

class ResolveRelativePathAction
{
    public function execute(array $from, array $to): ?string
    {
        if ($from === $to) {
            return null;
        }

        $commonDepth = 0;
        $maxDepth = min(count($from), count($to));

        for ($i = 0; $i < $maxDepth; $i++) {
            if ($from[$i] !== $to[$i]) {
                break;
            }
            $commonDepth++;
        }

        $relativeSegments = [];

        for ($i = $commonDepth; $i < count($from); $i++) {
            $relativeSegments[] = '..';
        }

        for ($i = $commonDepth; $i < count($to); $i++) {
            $relativeSegments[] = $to[$i];
        }

        return implode('/', $relativeSegments);
     }
}
