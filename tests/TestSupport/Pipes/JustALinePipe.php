<?php

namespace CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes;

use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use Exception;

/**
 * Render a line.
 */
class JustALinePipe extends AbstractPipe
{
    /**
     * Constructor.
     *
     * Properties are resolved using Laravel's dependency injection.
     *
     * @param PipelineOutput $output The object managing the output.
     */
    public function __construct(
        private PipelineOutput $output,
    ) {
    }

    /**
     * Run the pipe step.
     *
     * @return void
     * @throws Exception Every time.
     */
    public function run(): void
    {
        $this->output->reuseTextOrNew()->line('--------');
    }
}
