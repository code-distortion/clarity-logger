<?php

namespace CodeDistortion\ClarityLogger;

use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Clarity;
use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerPipelineException;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use CodeDistortion\ClarityLogger\Support\PipelineInputBuilder;
use CodeDistortion\ClarityLogger\Support\Support;
use CodeDistortion\Staticall\Staticall;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Render and log debugging information.
 *
 * @codingStandardsIgnoreStart
 *
 * @method static $this channel(string $channel) Set the channel to use.
 * @method static $this level(string $level) Set the level to use.
 * @method static $this debug(Throwable|string $toLog = null, array $context = []) Log a message or exception at "debug" level.
 * @method static $this info(Throwable|string $toLog = null, array $context = []) Log a message or exception at "info" level.
 * @method static $this notice(Throwable|string $toLog = null, array $context = []) Log a message or exception at "notice" level.
 * @method static $this warning(Throwable|string $toLog = null, array $context = []) Log a message or exception at "warning" level.
 * @method static $this error(Throwable|string $toLog = null, array $context = []) Log a message or exception at "error" level.
 * @method static $this critical(Throwable|string $toLog = null, array $context = []) Log a message or exception at "critical" level.
 * @method static $this alert(Throwable|string $toLog = null, array $context = []) Log a message or exception at "alert" level.
 * @method static $this emergency(Throwable|string $toLog = null, array $context = []) Log a message or exception at "emergency" level.
 * @method static $this log(Throwable|string $toLog, array $context = []) Log an exception or message.
 *
 * @codingStandardsIgnoreEnd
 */
class Logger
{
    use Staticall;



    /** @var string|null The channel to use. */
    private ?string $channel = null;

    /** @var string|null The level to use. */
    private ?string $level = null;



    /**
     * Set the channel to use.
     *
     * @param string $channel The channel to use.
     * @return $this
     */
    protected function callChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set the level to use.
     *
     * @param string $level The level to use.
     * @return $this
     * @throws ClarityLoggerInitialisationException Thrown when an invalid level is specified.
     */
    protected function callLevel(string $level): self
    {
        Support::ensureLevelIsValid($level);

        $this->level = $level;

        return $this;
    }



    /**
     * Log a message or exception at "debug" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callDebug(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_DEBUG;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "info" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callInfo(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_INFO;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "notice" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callNotice(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_NOTICE;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "warning" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callWarning(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_WARNING;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "error" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callError(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_ERROR;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "critical" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callCritical(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_CRITICAL;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "alert" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callAlert(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_ALERT;

        $this->internalLog($toLog, $context);

        return $this;
    }

    /**
     * Log a message or exception at "emergency" level.
     *
     * @param Throwable|string|null $toLog   The exception or message to log.
     * @param mixed[]               $context The context array to report.
     * @return $this
     */
    protected function callEmergency(Throwable|string $toLog = null, array $context = []): self
    {
        $this->level = Settings::REPORTING_LEVEL_EMERGENCY;

        $this->internalLog($toLog, $context);

        return $this;
    }



    /**
     * Log an exception or message.
     *
     * @param Throwable|string $toLog   The exception or message to log.
     * @param mixed[]          $context The context array to report.
     * @return $this
     */
    protected function callLog(Throwable|string $toLog, array $context = []): self
    {
        $this->internalLog($toLog, $context);

        return $this;
    }





    /**
     * Report an exception or message.
     *
     * @param Throwable|string|null $toLog              The exception or message to log.
     * @param mixed[]               $callerContextArray The context array to report.
     * @return void
     */
    private function internalLog(Throwable|string|null $toLog, array $callerContextArray = []): void
    {
        if (is_null($toLog)) {
            return;
        }

        $callerMessage = $toLog instanceof Throwable ? null : $toLog;
        $exception = $toLog instanceof Throwable ? $toLog : null;

        $clarityContext = null;
        if (class_exists(Clarity::class)) {

            $clarityContext = $exception
                ? Clarity::getExceptionContext($exception)
                : Clarity::buildContextHere(4);

            // the Context's channel and level won't be used by Logger,
            // but they are set here anyway so their values match, to avoid possible confusion later
            if ($this->channel) {
                $clarityContext->setChannels([$this->channel]);
            }
            if ($this->level) {
                $clarityContext->setLevel($this->level);
            }
        }

        $pipelineInputs = (new PipelineInputBuilder(
            $this->channel,
            $this->level,
            $callerMessage,
            $exception,
            $callerContextArray,
            $clarityContext,
            CarbonImmutable::now('UTC'),
        ))->build();

        $this->performLogging($pipelineInputs);
    }



    /**
     * Perform the logging, based on given PipelineInputs.
     *
     * @param PipelineInput[] $pipelineInputs The PipelineInputs to use.
     * @return void
     */
    private function performLogging(array $pipelineInputs): void
    {
        foreach ($pipelineInputs as $input) {

            $channel = $input->getChannel();
            $level = $input->getLevel();

            $rendererClass = $input->resolveRendererClass($channel);
            $output = self::newRenderer($rendererClass)->render($input);

            Log::channel($channel)->log($level, $output);

            // actually call the "$level" method (e.g. Log::debug(..)) here as lets us check what's called more easily
            // when testing. Mock can specify Log::shouldReceive($level)->once()->andReturnSelf();
            // instead of Log::shouldReceive('log')->withArgs([$level, $message])->once()->andReturnSelf();
            // because the $message variable isn't fully known
//            Log::channel($channel)->$level($output);
        }
    }

    /**
     * Instantiate a new renderer to use.
     *
     * @param class-string $class The renderer class to use.
     * @return RendererInterface
     * @throws ClarityLoggerPipelineException When an invalid renderer class is used.
     */
    private static function newRenderer(string $class): RendererInterface
    {
        $renderer = Framework::depInj()->make($class);

        return $renderer instanceof RendererInterface
            ? $renderer
            : throw ClarityLoggerPipelineException::invalidRendererClass($class);
    }
}
