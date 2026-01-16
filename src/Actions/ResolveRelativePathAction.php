<?php

namespace Spatie\TypeScriptTransformer\Actions;

class ResolveRelativePathAction
{
    public function execute(string $from, string $to): ?string
    {
        $from = $this->toSegments($from);
        $to = $this->toSegments($to);

        if ($from === $to) {
            return null;
        }

        array_pop($from);

        if ($to[array_key_last($to)] === 'index') {
            array_pop($to);
        }

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

        if (count($relativeSegments) === 0 && $commonDepth < count($to)) {
            $relativeSegments[] = '.';
        }

        $hasSuffixedSegments = false;

        for ($i = $commonDepth; $i < count($to); $i++) {
            $relativeSegments[] = $to[$i];

            $hasSuffixedSegments = true;
        }

        $relativePath = implode('/', $relativeSegments);

        return $hasSuffixedSegments ? $relativePath : $relativePath.'/';
    }

    /** @return array<int, string> */
    protected function toSegments(string $path): array
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        $lastIndex = array_key_last($segments);

        $segments[$lastIndex] = pathinfo($segments[$lastIndex], PATHINFO_FILENAME);

        return $segments;
    }
}
