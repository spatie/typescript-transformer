<?php

namespace Spatie\TypeScriptTransformer\Formatters;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BiomeFormatter implements Formatter
{
    public function format(string $file): void
    {
        $process = new Process(['npx', '--yes', '@biomejs/biome', 'format', '--write', $file]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
