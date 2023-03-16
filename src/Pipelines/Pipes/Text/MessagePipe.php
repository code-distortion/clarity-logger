<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Helpers\CallerLocationHelper;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use CodeDistortion\ClarityLogger\Settings;

/**
 * Render the message the user specified.
 */
class MessagePipe extends AbstractPipe
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
        if (is_null($this->input->getCallerMessage())) {
            return false;
        }

        return mb_strlen($this->input->getCallerMessage()) > 0;
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

        $location = CallerLocationHelper::renderLocation(
            $this->input->getProjectRootDir()
        );

        $location = ltrim((string) $location, DIRECTORY_SEPARATOR);

        $callerMessage = (string) $this->input->getCallerMessage();

        $table = $this->output->reuseTableOrNew();
        $table->row('message', "\"$callerMessage\"");
        $table->row(Settings::INDENT1 . 'location', $location);
    }
}
