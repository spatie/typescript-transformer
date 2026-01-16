<?php

namespace Spatie\TypeScriptTransformer\Formatters;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class EslintFormatter implements Formatter
{
    public function format(array $files): void
    {
        $process = new Process(['npx', '--yes', 'eslint', '--fix', '--no-ignore', ...$files]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
