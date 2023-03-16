<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Helpers\IsReportingHelper;
use CodeDistortion\ClarityLogger\Output\TextOutput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use Throwable;

/**
 * Render the title.
 */
class TitlePipe extends AbstractPipe
{
    /** @var TextOutput The Text that can be used later to render internal exceptions. */
    private TextOutput $text;

    /** @var boolean|null Override the check-if-reporting check - for use by tests. */
    private ?bool $overrideCheckIfReporting = null;



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
     * Run the pipe step.
     *
     * @return void
     */
    public function run(): void
    {
        // "reserve" these spots, so they can be added to later if exceptions occur
        $this->text = $this->output->newText();
    }



    /**
     * Run a pipe step. This is called when no exceptions occurred while building the report.
     *
     * @return void
     */
    public function noInternalExceptionOccurred(): void
    {
        if (!$this->input->getException()) {
            $this->text->line('CUSTOM MESSAGE:');
            return;
        }

        // allow tests to override
        $check = !is_null($this->overrideCheckIfReporting)
            ? $this->overrideCheckIfReporting
            : IsReportingHelper::checkIfReporting();

        $check
            ? $this->text->line('EXCEPTION (CAUGHT):')
            : $this->text->line('EXCEPTION (UNCAUGHT):');
    }

    /**
     * Run a pipe step. This is called when an exception occurred while building the report.
     *
     * @param Throwable[] $exceptions The exceptions that occurred while building the report.
     * @return void
     */
    public function internalExceptionOccurred(array $exceptions): void
    {
        if (!$this->input->getException()) {
            $this->text->line('CUSTOM MESSAGE:');
            return;
        }

        // allow tests to override
        $check = !is_null($this->overrideCheckIfReporting)
            ? $this->overrideCheckIfReporting
            : IsReportingHelper::checkIfReporting();

        $check
            ? $this->text->line('ORIGINAL EXCEPTION (CAUGHT):')
            : $this->text->line('ORIGINAL EXCEPTION (UNCAUGHT):');
    }
}
