<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeDistortion\ClarityLogger\Helpers\PointInTimeHelper;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;

/**
 * Render the time the exception (or message being reported) occurred.
 */
class OccurredAtPipe extends AbstractPipe
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
        return !is_null($this->input->getOccurredAt());
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

        /** @var Carbon|CarbonImmutable $occurredAt */
        $occurredAt = $this->input->getOccurredAt();

        $pointInTime = new PointInTimeHelper($occurredAt);

        $occurredAtLines = $pointInTime->renderAsString(
            $this->input->getDateTimeFormat(),
            $this->input->getTimezones(),
        );

        $this->output->reuseTableOrNew()->row('date/time', $occurredAtLines);
    }
}
