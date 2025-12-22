<?php

namespace Spatie\TypeScriptTransformer\Runners;

use Closure;
use Spatie\TypeScriptTransformer\FileSystemWatcher;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Runner
{
    /**
     * @param Closure(bool $watch):string $workerCommand
     */
    public function __construct(
        protected Closure $workerCommand,
    ) {
    }

    public function run(
        Logger $logger,
        TypeScriptTransformerConfig $config,
        bool $watch,
        bool $isWorker,
        bool $forceDirectWorker = false,
    ): int {
        if ($this->shouldStartWorkerProcess($isWorker, $watch, $forceDirectWorker)) {
            return $this->keepWorkerRunning($logger, $watch);
        }

        TypeScriptTransformer::create(
            config: $config,
            logger: $logger,
            watch: $watch
        )->execute();

        $logger->info('All done!');

        return 0;
    }

    protected function shouldStartWorkerProcess(
        bool $isWorker,
        bool $watch,
        bool $forceDirectWorker
    ): bool {
        if ($watch === false || $forceDirectWorker === true) {
            return false;
        }

        return $isWorker === false;
    }

    protected function keepWorkerRunning(
        Logger $logger,
        bool $watch,
    ): int {
        // TODO: since in between refreshes the process is restarted and thus the (Laravel) application is reloaded,
        // it could be that some bug was intriduced in the application that causes the worker to crash. Then we
        // won't have a worker anymore. Find a way to communicate between the processes that a full refresh is desired
        // when this is the case. Try to start a new worker process in the background and wait until it is ready to
        // start watching. If it is ready, stop the current watch worker and transfer control to the new worker.
        // If it is not ready, wait until it is ready and then stop the current worker or provide a message
        // to the user we were not able to restart the worker.

        while (true) {
            $endedProcess = $this->startWorkerProcess($logger, $watch);

            $exitCode = $endedProcess->getExitCode();

            if ($exitCode === FileSystemWatcher::EXIT_CODE_COMPLETE_REFRESH) {
                continue;
            }

            if ($exitCode === 0) {
                $logger->info('Process completed successfully');
            } else {
                $logger->error($endedProcess->getErrorOutput(), "Process failed with exit code: $exitCode");
            }

            return $exitCode;
        }
    }

    protected function startWorkerProcess(
        Logger $logger,
        bool $watch,
    ): Process {
        $phpBinary = (new PhpExecutableFinder())->find();
        $command = ($this->workerCommand)($watch);

        $process = Process::fromShellCommandline("$phpBinary $command");

        $process->start();

        $logger->info('Watching for changes...');

        while ($process->isRunning()) {
            if ($stdout = $process->getIncrementalOutput()) {
                $logger->info($stdout);
            }

            if ($stderr = $process->getIncrementalErrorOutput()) {
                $logger->error($stderr);
            }

            usleep(100000);
        }

        if ($remainingOutput = $process->getOutput()) {
            $lines = explode("\n", trim($remainingOutput));
            $processedLines = array_slice($lines, -10);

            foreach ($processedLines as $line) {
                if (! empty($line)) {
                    $logger->info($line);
                }
            }
        }

        return $process;
    }
}
