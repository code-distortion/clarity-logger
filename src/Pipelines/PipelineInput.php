<?php

namespace CodeDistortion\ClarityLogger\Pipelines;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Context;
use Throwable;

/**
 * Holds the various inputs, to be consumed by the pipeline.
 */
class PipelineInput
{
    /**
     * Constructor.
     *
     * @param string                      $projectRootDir     The project root-dir.
     * @param boolean                     $runningInConsole   Whether the code is running from the console or not.
     * @param string|null                 $consoleCommand     The console command being run.
     * @param class-string                $defaultRenderer    The default renderer to use.
     * @param array<string, class-string> $channelRenderers   The renderers for a particular channels.
     * @param string[]                    $timezones          The timezones to render dates/times in.
     * @param string[]                    $dateTimeFormat     The format to render dates/times in.
     * @param string                      $prefix             The prefix to use.
     * @param boolean                     $useCallStackOrder  The "use call stack order" setting.
     * @param string                      $channel            The channel being reported to.
     * @param string                      $level              The reporting level being used.
     * @param string|null                 $callerMessage      The caller-specified message.
     * @param Throwable|null              $exception          The exception to report.
     * @param mixed[]                     $callerContextArray The array of context details the caller reported.
     * @param Context|null                $clarityContext     The Clarity Context object to report.
     * @param Carbon|CarbonImmutable|null $occurredAt         When the exception occurred.
     */
    public function __construct(
        private string $projectRootDir,
        private bool $runningInConsole,
        private ?string $consoleCommand,
        private string $defaultRenderer,
        private array $channelRenderers,
        private array $timezones,
        private array $dateTimeFormat,
        private string $prefix,
        private bool $useCallStackOrder,
        private string $channel,
        private string $level,
        private ?string $callerMessage,
        private ?Throwable $exception,
        private array $callerContextArray,
        private ?Context $clarityContext,
        private Carbon|CarbonImmutable|null $occurredAt,
    ) {
    }



    /**
     * Get the project root-dir.
     *
     * @return string
     */
    public function getProjectRootDir(): string
    {
        return $this->projectRootDir;
    }

    /**
     * Find out if the code is currently running from the console.
     *
     * @return boolean
     */
    public function getRunningInConsole(): bool
    {
        return $this->runningInConsole;
    }

    /**
     * Get the console command being run.
     *
     * @return ?string
     */
    public function getConsoleCommand(): ?string
    {
        return $this->consoleCommand;
    }

    /**
     * Work out which renderer to use, based on the channel.
     *
     * @param string $channel The channel being reported to.
     * @return class-string
     */
    public function resolveRendererClass(string $channel): string
    {
        return $this->channelRenderers[$channel]
            ?? $this->defaultRenderer;
    }

    /**
     * Get the timezones to render dates/times in.
     *
     * @return string[]
     */
    public function getTimezones(): array
    {
        return $this->timezones;
    }

    /**
     * Get the format to render dates/times in.
     *
     * @return string[]
     */
    public function getDateTimeFormat(): array
    {
        return $this->dateTimeFormat;
    }

    /**
     * Get the prefix to use.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Get the "use call stack order" setting.
     *
     * @return boolean
     */
    public function getUseCallStackOrder(): bool
    {
        return $this->useCallStackOrder;
    }





    /**
     * Get the channel.
     *
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * Get the reporting level.
     *
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Get the caller's message that's being reported.
     *
     * @return string|null
     */
    public function getCallerMessage(): ?string
    {
        return $this->callerMessage;
    }

    /**
     * Get the exception being reported.
     *
     * @return Throwable|null
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * Get the array of context details the caller reported.
     *
     * @return mixed[]
     */
    public function getCallerContextArray(): array
    {
        return $this->callerContextArray;
    }

    /**
     * Get the Clarity Context object being reported.
     *
     * @return Context|null
     */
    public function getClarityContext(): ?Context
    {
        return $this->clarityContext;
    }

    /**
     * Get the time the exception occurred.
     *
     * @return Carbon|CarbonImmutable|null
     */
    public function getOccurredAt(): Carbon|CarbonImmutable|null
    {
        return $this->occurredAt;
    }
}
