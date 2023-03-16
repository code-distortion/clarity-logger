<?php

namespace CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes;

use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use Exception;
use Throwable;

/**
 * Trigger an exception in the noInternalExceptionOccurred method.
 */
class TriggerExceptionDuringInternalExceptionOccurredMethodPipe extends AbstractPipe
{
    /**
     * Run the pipe step.
     *
     * @return void
     */
    public function run(): void
    {
    }

    /**
     * Run a pipe step. This is called when an exception occurred while building the report.
     *
     * @param Throwable[] $exceptions The exceptions that occurred while building the report.
     * @return void
     * @throws Exception Every time.
     */
    public function internalExceptionOccurred(array $exceptions): void
    {
        throw new Exception();
    }
}
