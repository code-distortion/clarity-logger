<?php

namespace CodeDistortion\ClarityLogger\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Context;
use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
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
     * @param string|null            $callerMessage      The caller-specified message to report.
     * @param Throwable|null         $exception          The exception to report.
     * @param mixed[]                $callerContextArray The array of context details the caller reported.
     * @param Context|null           $clarityContext     The Clarity Context object to report.
     * @param Carbon|CarbonImmutable $occurredAt         When the exception occurred.
     */
    public function __construct(
        private ?string $channel,
        private ?string $level,
        private ?string $callerMessage,
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

        $channels = $this->resolveChannels($config->getFrameworkDefaultChannels());

        Support::ensureLevelIsValid($level = $this->resolveLevel());

        $pipelineInputs = [];
        foreach ($channels as $channel) {

            $runningInConsole = $config->runningInConsole();

            $pipelineInputs[] = new PipelineInput(
                $config->getProjectRootDir(),
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
                $this->callerMessage,
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
     * @param string[] $defaultChannels The default channels to use.
     * @return string[]
     */
    private function resolveChannels(array $defaultChannels): array
    {
        // if present, use the channel explicitly specified by the caller
        if ((is_string($this->channel)) && (mb_strlen($this->channel))) {
            return [$this->channel];
        }

        // use Clarity Context's value if it's present
        return $this->clarityContext?->getChannels()
            ?: $defaultChannels;
    }

    /**
     * Work out which reporting level to use.
     *
     * @return string
     */
    private function resolveLevel(): string
    {
        if ($this->level) {
            return $this->level;
        }

        if ($this->clarityContext?->getLevel()) {
            return $this->clarityContext->getLevel();
        }

        return $this->exception
            ? Framework::config()->getDefaultExceptionLevel()
            : Framework::config()->getDefaultMessageLevel();
    }
}
