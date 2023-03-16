<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Helpers\ExceptionHelper;
use CodeDistortion\ClarityLogger\Output\TableOutput;
use CodeDistortion\ClarityLogger\Output\TextOutput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use Throwable;

/**
 * Render the exception that occurred.
 */
class InternalExceptionsPipe extends AbstractPipe
{
    /** @var TextOutput The Text that can be used later to render internal exceptions. */
    private TextOutput $text;

    /** @var TableOutput The TableOutput that can be used later to render internal exceptions. */
    private TableOutput $table;



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
        $this->table = $this->output->newTable();
    }



    /**
     * Run a pipe step. This is called when an exception occurred while building the report.
     *
     * @param Throwable[] $exceptions The exceptions that occurred while building the report.
     * @return void
     */
    public function internalExceptionOccurred(array $exceptions): void
    {
        if ($this->input->getException()) {
            count($exceptions) == 1
                ? $this->text->line('NEW EXCEPTION (that occurred when building the report)')
                : $this->text->line('NEW EXCEPTIONS (that occurred when building the report)');
        } else {
            count($exceptions) == 1
                ? $this->text->line('EXCEPTION (that occurred when building the report)')
                : $this->text->line('EXCEPTIONS (that occurred when building the report)');
        }

        $baseDir = $this->input->getProjectRootDir();

        $count = 0;
        foreach ($exceptions as $e) {
            if ($count++) {
                $this->table->blankRow();
            }
            count($exceptions) > 1
                ? ExceptionHelper::renderExceptionToTable($this->table, $e, $baseDir, $count)
                : ExceptionHelper::renderExceptionToTable($this->table, $e, $baseDir);

        }
    }
}
