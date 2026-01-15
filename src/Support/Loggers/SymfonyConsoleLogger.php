<?php

namespace Spatie\TypeScriptTransformer\Support\Loggers;

use Symfony\Component\Console\Output\OutputInterface;

class SymfonyConsoleLogger extends ArrayLogger
{
    public function __construct(
        protected OutputInterface $output,
    ) {
    }

    public function error(mixed $item, ?string $title = null): void
    {
        $message = $this->formatMessage($item, $title);

        $this->output->writeln("<error>{$message}</error>");
    }

    public function info(mixed $item, ?string $title = null): void
    {
        $message = $this->formatMessage($item, $title);

        $this->output->writeln("<info>{$message}</info>");
    }

    public function warning(mixed $item, ?string $title = null): void
    {
        $message = $this->formatMessage($item, $title);

        $this->output->writeln("<comment>{$message}</comment>");
    }

    public function debug(mixed $item, ?string $title = null): void
    {
        if (! $this->output->isVerbose()) {
            return;
        }

        $message = $this->formatMessage($item, $title);

        $this->output->writeln($message);
    }

    protected function formatMessage(mixed $item, ?string $title): string
    {
        $message = $this->mixedToString($item);

        if ($title !== null) {
            return "[{$title}] {$message}";
        }

        return $message;
    }
}
