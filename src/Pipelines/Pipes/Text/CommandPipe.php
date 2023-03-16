<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use CodeDistortion\ClarityLogger\Settings;

/**
 * Render the time the exception (or message being reported) occurred.
 */
class CommandPipe extends AbstractPipe
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
        return $this->input->getRunningInConsole();
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

        /** @var string $command Command is a string by this point. */
        $command = $this->input->getConsoleCommand();
        $command = mb_strlen($command)
            ? $command
            : '(unknown)';

        $this->output->reuseTableOrNew()->row('command', $command);
        $this->output->reuseTableOrNew()->row(Settings::INDENT1 . 'user', get_current_user());

        $this->addTraceIdentifiers();
    }



    /**
     * Add the trace identifiers to the table.
     *
     * @return void
     */
    private function addTraceIdentifiers(): void
    {
        $context = $this->input->getClarityContext();
        if (!$context) {
            return;
        }

        $identifiers = $this->buildTraceIdentifiersList($context->getTraceIdentifiers());
        if (!$identifiers) {
            return;
        }

        $this->output->reuseTableOrNew()->row(
            Settings::INDENT1 . (count($identifiers) == 1 ? 'trace-id' : 'trace-ids'),
            implode(PHP_EOL, $identifiers)
        );
    }

    /**
     * Build a readable list of trace identifiers.
     *
     * @param array<string,string|integer> $traceIdentifiers The trace identifiers to build a list of.
     * @return string[]
     */
    private function buildTraceIdentifiersList(array $traceIdentifiers): array
    {
        $identifiers = [];
        foreach ($traceIdentifiers as $name => $id) {
            $identifiers[] = mb_strlen($name)
                ? "$name: $id"
                : "$id";
        }

        return $identifiers;
    }
}
