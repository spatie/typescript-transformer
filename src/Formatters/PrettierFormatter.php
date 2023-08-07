<?php

namespace Spatie\TypeScriptTransformer\Formatters;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PrettierFormatter implements Formatter
{
    public function format(array $files): void
    {
        $process = new Process(['npx', '--yes', 'prettier', '--write', ...$files]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
