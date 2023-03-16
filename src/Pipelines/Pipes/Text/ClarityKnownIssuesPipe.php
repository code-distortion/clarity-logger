<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityContext\Context;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;

/**
 * Render the "known" issues the exception relates to (reported by Clarity Context).
 */
class ClarityKnownIssuesPipe extends AbstractPipe
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
        if (is_null($this->input->getClarityContext())) {
            return false;
        }
        if (!count($this->input->getClarityContext()->getKnown())) {
            return false;
        }
        return true;
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

        /** @var Context $context It is a Context object by this point. */
        $context = $this->input->getClarityContext();

        $this->output->reuseTableOrNew()->row('known', implode(PHP_EOL, $context->getKnown()));
    }
}
