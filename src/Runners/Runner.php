<?php

namespace Spatie\TypeScriptTransformer\Runners;

use Closure;
use Spatie\TypeScriptTransformer\Enums\RunnerMode;
use Spatie\TypeScriptTransformer\FileSystemWatcher;
use Spatie\TypeScriptTransformer\Support\Console\Logger;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Runner
{
    public const WORKER_READY_SIGNAL = '[TYPESCRIPT_TRANSFORMER_READY]';

    /**
     * @param Closure(bool $watch):string $workerCommand
     */
    public function __construct(
        protected Closure $workerCommand,
        protected int $workerStartupTimeoutSeconds = 30,
    ) {
    }

    public function run(
        Logger $logger,
        TypeScriptTransformerConfig $config,
        RunnerMode $mode,
    ): int {
        // Todo review all of this

        return match ($mode) {
            RunnerMode::Master => $this->runAsMaster($logger),
            RunnerMode::Worker => $this->runAsWorker($logger, $config),
            RunnerMode::Direct => $this->runDirect($logger, $config),
        };
    }

    protected function runAsMaster(Logger $logger): int
    {
        $currentWorker = null;
        $errorShownForFailedWorker = false;

        while (true) {
            $newWorker = $this->startWorkerProcessInBackground();

            $isReady = $this->waitForWorkerReady($newWorker, $logger, $currentWorker);

            if (! $isReady) {
                $this->handleFailedWorkerStart($newWorker, $logger, $errorShownForFailedWorker);

                $errorShownForFailedWorker = true;

                $newWorker->stop();

                $sleepDuration = ($currentWorker === null || ! $currentWorker->isRunning()) ? 5 : 1;

                sleep($sleepDuration);

                continue;
            }

            $errorShownForFailedWorker = false;

            if ($currentWorker !== null && $currentWorker->isRunning()) {
                $currentWorker->stop();
            }

            $currentWorker = $newWorker;

            $logger->info('Worker started successfully, watching for changes...');

            $exitCode = $this->monitorWorkerProcess($currentWorker, $logger);

            if ($exitCode !== FileSystemWatcher::EXIT_CODE_COMPLETE_REFRESH) {
                $this->logWorkerCompletion($logger, $exitCode, $currentWorker);

                return $exitCode;
            }

            $logger->info('Complete refresh requested, restarting worker...');
        }
    }

    protected function runAsWorker(
        Logger $logger,
        TypeScriptTransformerConfig $config,
    ): int {
        TypeScriptTransformer::create(
            config: $config,
            logger: $logger,
            watch: true
        )->execute();

        return 0;
    }

    protected function runDirect(
        Logger $logger,
        TypeScriptTransformerConfig $config,
    ): int {
        TypeScriptTransformer::create(
            config: $config,
            logger: $logger,
            watch: false
        )->execute();

        $logger->info('All done!');

        return 0;
    }

    protected function handleFailedWorkerStart(
        Process $worker,
        Logger $logger,
        bool $errorAlreadyShown,
    ): void {
        if ($errorAlreadyShown) {
            return;
        }

        $logger->error(
            $worker->getErrorOutput(),
            'Worker failed to start. Waiting for application to be fixed...'
        );
    }

    protected function logWorkerCompletion(
        Logger $logger,
        int $exitCode,
        Process $worker,
    ): void {
        if ($exitCode === 0) {
            $logger->info('Process completed successfully');

            return;
        }

        $logger->error($worker->getErrorOutput(), "Process failed with exit code: $exitCode");
    }

    protected function startWorkerProcessInBackground(): Process
    {
        $phpBinary = (new PhpExecutableFinder())->find();
        $command = ($this->workerCommand)(true);

        $process = Process::fromShellCommandline("$phpBinary $command");
        $process->start();

        return $process;
    }

    protected function waitForWorkerReady(
        Process $process,
        Logger $logger,
        ?Process $currentWorker = null,
    ): bool {
        $startTime = time();
        $outputBuffer = '';

        while ($process->isRunning()) {
            $output = $process->getIncrementalOutput();
            $outputBuffer .= $output;

            if (str_contains($outputBuffer, self::WORKER_READY_SIGNAL)) {
                return true;
            }

            if ($stderr = $process->getIncrementalErrorOutput()) {
                $logger->error($stderr);
            }

            $this->monitorOldWorkerOutput($currentWorker, $logger);

            if (time() - $startTime > $this->workerStartupTimeoutSeconds) {
                return false;
            }

            usleep(100000);
        }

        return false;
    }

    protected function monitorOldWorkerOutput(
        ?Process $worker,
        Logger $logger,
    ): void {
        if ($worker === null || ! $worker->isRunning()) {
            return;
        }

        $this->logProcessOutput($worker, $logger);
    }

    protected function logProcessOutput(
        Process $process,
        Logger $logger,
    ): void {
        if ($stdout = $process->getIncrementalOutput()) {
            $lines = explode("\n", $stdout);

            foreach ($lines as $line) {
                if (empty($line) || str_contains($line, self::WORKER_READY_SIGNAL)) {
                    continue;
                }

                $logger->info($line);
            }
        }

        if ($stderr = $process->getIncrementalErrorOutput()) {
            $logger->error($stderr);
        }
    }

    protected function monitorWorkerProcess(
        Process $process,
        Logger $logger,
    ): int {
        while ($process->isRunning()) {
            $this->logProcessOutput($process, $logger);

            usleep(100000);
        }

        return $process->getExitCode() ?? 1;
    }
}
