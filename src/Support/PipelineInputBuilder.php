<?php

namespace CodeDistortion\ClarityLogger\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Context;
use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use Throwable;

/**
 * Helper to build PipelineInput objects.
 */
class PipelineInputBuilder
{
    /**
     * Constructor.
     *
     * @param string|null            $channel            The caller-specified channel.
     * @param string|null            $level              The caller-specified reporting level.
     * @param string|null            $message            The caller-specified message to report.
     * @param Throwable|null         $exception          The exception to report.
     * @param mixed[]                $callerContextArray The array of context details the caller reported.
     * @param Context|null           $clarityContext     The Clarity Context object to report.
     * @param Carbon|CarbonImmutable $occurredAt         When the exception occurred.
     */
    public function __construct(
        private ?string $channel,
        private ?string $level,
        private ?string $message,
        private ?Throwable $exception,
        private array $callerContextArray,
        private ?Context $clarityContext,
        private Carbon|CarbonImmutable $occurredAt,
    ) {
    }


    /**
     * Build the PipelineInput objects described by the input.
     *
     * @return PipelineInput[]
     * @throws ClarityLoggerInitialisationException When an invalid level is specified.
     */
    public function build(): array
    {
        $config = Framework::config();

        $channels = $this->resolveChannels($config->getDefaultChannel());

        Support::ensureLevelIsValid($level = $this->resolveLevel());

        $pipelineInputs = [];
        foreach ($channels as $channel) {

            $runningInConsole = $config->runningInConsole();

            $pipelineInputs[] = new PipelineInput(
                $config->getProjectRoot(),
                $runningInConsole,
                $runningInConsole ? $config->getConsoleCommand() : null,
                $config->getDefaultRenderer(),
                $config->getRenderersPerChannel(),
                $config->getTimezones(),
                $config->getDateTimeFormat(),
                $config->getPrefix(),
                $config->getUseCallStackOrder(),
                $channel,
                $level,
                $this->message,
                $this->exception,
                $this->callerContextArray,
                $this->clarityContext,
                $this->occurredAt
            );
        }

        return $pipelineInputs;
    }



    /**
     * Work out which channel to use.
     *
     * @param string $defaultChannel The default channel to use.
     * @return string[]
     */
    private function resolveChannels(string $defaultChannel): array
    {
        if ((is_string($this->channel)) && (mb_strlen($this->channel))) {
            return [$this->channel];
        }

        return $this->clarityContext?->getChannels()
            ?: [$defaultChannel];
    }

    /**
     * Work out which reporting level to use.
     *
     * @return string
     */
    private function resolveLevel(): string
    {
        if ((is_string($this->level)) && (mb_strlen($this->level))) {
            return $this->level;
        }

        if ($this->exception) {
            // when Clarity Context is present, it overrides the reporting level
            return $this->clarityContext?->getLevel()
                ?: Framework::config()->getDefaultExceptionLevel();
        }

        return Framework::config()->getDefaultMessageLevel();
    }
}
