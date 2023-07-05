<?php

namespace Spatie\TypeScriptTransformer\Actions;

class ResolveRelativePathAction
{
    public function execute(
        array $currentNamespaceSegments,
        array $requestedNamespaceSegments,
    ): ?string {
        $currentNamespaceSegments = array_values($currentNamespaceSegments);
        $requestedNamespaceSegments = array_values($requestedNamespaceSegments);

        $maxIndex = max(
            count($currentNamespaceSegments),
            count($requestedNamespaceSegments)
        );

        for ($i = 0; $i < $maxIndex; $i++) {
            if (
                array_key_exists($i, $currentNamespaceSegments)
                && array_key_exists($i, $requestedNamespaceSegments)
                && $currentNamespaceSegments[$i] === $requestedNamespaceSegments[$i]) {
                unset($currentNamespaceSegments[$i]);
                unset($requestedNamespaceSegments[$i]);
            }
        }

        $currentNamespaceSegments = array_values($currentNamespaceSegments);
        $requestedNamespaceSegments = array_values($requestedNamespaceSegments);

        if (empty($currentNamespaceSegments) && empty($requestedNamespaceSegments)) {
            return null;
        }

        $segments = ['.'];

        foreach ($currentNamespaceSegments as $i) {
            $segments[] = '..';
        }

        array_push($segments, ...$requestedNamespaceSegments);

        return implode('/', $segments);
    }
}
