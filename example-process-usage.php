<?php

// Example usage of ProcessRunner with real-time logging

use Spatie\TypeScriptTransformer\Support\Console\ConsoleLogger;
use Spatie\TypeScriptTransformer\Support\ProcessRunner;

// Create a logger (you can use any logger implementation)
$logger = new ConsoleLogger();

// Create process runner
$processRunner = new ProcessRunner($logger);

// Example 1: Run a simple command with real-time output streaming
echo "=== Running 'ls -la' with real-time logging ===\n";
$exitCode = $processRunner->runWithRealTimeLogging('ls -la');
echo "Exit code: $exitCode\n\n";

// Example 2: Run a PHP script with real-time output
echo "=== Running PHP version check with real-time logging ===\n";
$exitCode = $processRunner->runWithRealTimeLogging('php --version');
echo "Exit code: $exitCode\n\n";

// Example 3: Run command and get result object (non-streaming)
echo "=== Running command and getting result object ===\n";
$result = $processRunner->run(['php', '--version']);
echo "Success: " . ($result->isSuccessful() ? 'Yes' : 'No') . "\n";
echo "Output lines count: " . count($result->getOutputLines()) . "\n";
echo "Command: {$result->command}\n\n";

// Example 4: Run a long-running process (like watching files)
echo "=== Example of running a Symfony process (this would be your actual use case) ===\n";
// $processRunner->runWithRealTimeLogging('php bin/console app:watch --timeout=0');
