<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;

/**
 * Render the context details the user provided.
 */
class ContextArrayPipe extends AbstractPipe
{
    /**
     * Constructor.
     *
     * Properties are resolved using Laravel's dependency injection.
     *
     * @param PipelineInput  $input  The input being reported.
     * @param PipelineOutput $output The object managing the output.
     */
    public function __construct(
        private PipelineInput $input,
        private PipelineOutput $output,
    ) {
    }



    /**
     * Determine if this pipe step should be run.
     *
     * @return boolean
     */
    private function shouldRun(): bool
    {
        return count($this->input->getCallerContextArray()) > 0;
    }



    /**
     * Run the pipe step.
     *
     * @return void
     */
    public function run(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $this->output->reuseTableOrNew()->row('context', $this->input->getCallerContextArray());
    }
}
