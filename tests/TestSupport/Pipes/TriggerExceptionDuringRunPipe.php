<?php

namespace CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes;

use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use Exception;

/**
 * Render the user's details.
 */
class TriggerExceptionDuringRunPipe extends AbstractPipe
{
    /**
     * Run the pipe step.
     *
     * @return void
     * @throws Exception Every time.
     */
    public function run(): void
    {
        throw new Exception('Something happened');
    }
}
