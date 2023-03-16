<?php

namespace CodeDistortion\ClarityLogger\Tests\Support\Pipes;

use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use Exception;

/**
 * Trigger an exception in the noInternalExceptionOccurred method.
 */
class TriggerExceptionDuringNoInternalExceptionOccurredMethodPipe extends AbstractPipe
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
     * Run a pipe step, when no exceptions occurred while building the report.
     *
     * @return void
     * @throws Exception Every time.
     */
    public function noInternalExceptionOccurred(): void
    {
        throw new Exception('Something happened');
    }
}
