<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes;

use Throwable;

/**
 * Interface for SimplePipe.
 */
interface PipeInterface
{
    /**
     * Run the pipe step.
     *
     * @return void
     */
    public function run(): void;

    /**
     * Run a pipe step, when no exceptions occurred while building the report.
     *
     * @return void
     */
    public function noInternalExceptionOccurred(): void;

    /**
     * Run a pipe step, when an exception occurred while building the report.
     *
     * @param Throwable[] $exceptions The exceptions that occurred while building the report.
     * @return void
     */
    public function internalExceptionOccurred(array $exceptions): void;
}
