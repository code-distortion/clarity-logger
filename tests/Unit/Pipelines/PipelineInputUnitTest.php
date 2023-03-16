<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Pipelines;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Clarity;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use CodeDistortion\ClarityLogger\Tests\TestSupport\ExceptionDTO;
use CodeDistortion\ClarityLogger\Tests\TestSupport\UserModel;
use Exception;

/**
 * Test the PipelineInput class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PipelineInputUnitTest extends LaravelTestCase
{
    /**
     * Test that the PipelineInput returns the correct values.
     *
     * @return void
     */
    public static function test_pipeline_input_getters(): void
    {
        // stop if the Clarity Context package isn't installed
        // Note: it's ok that this test isn't performed (Github actions runs the tests with and without)
        if (!class_exists(Clarity::class)) {
            self::assertTrue(true);
            return;
        }



        $projectRootDir = '/abc/def/';
        $runningInConsole = true;
        $consoleCommand = 'run this';
        $defaultRenderer = ExceptionDTO::class; // just some class
        $channelRenderers = ['a-channel' => UserModel::class]; // just some class
        $timezones = ['UTC'];
        $dateTimeFormat = ['Y-m-d', 'H:i:s'];
        $prefix = 'prefix >';
        $useCallStackOrder = true;
        $channel = 'some-channel';
        $level = Settings::REPORTING_LEVEL_ERROR;
        $message = 'some message';
        $exception = new Exception();
        $callerContextArray = ['a' => 'b'];
        $clarityContext = Clarity::buildContextHere();
        $occurredAt = CarbonImmutable::now();

        $pipelineInput = new PipelineInput(
            $projectRootDir,
            $runningInConsole,
            $consoleCommand,
            $defaultRenderer,
            $channelRenderers,
            $timezones,
            $dateTimeFormat,
            $prefix,
            $useCallStackOrder,
            $channel,
            $level,
            $message,
            $exception,
            $callerContextArray,
            $clarityContext,
            $occurredAt,
        );

        self::assertSame($projectRootDir, $pipelineInput->getProjectRootDir());
        self::assertSame($runningInConsole, $pipelineInput->getRunningInConsole());
        self::assertSame($consoleCommand, $pipelineInput->getConsoleCommand());
        self::assertSame(UserModel::class, $pipelineInput->resolveRendererClass('a-channel'));
        self::assertSame(ExceptionDTO::class, $pipelineInput->resolveRendererClass('b-channel'));
        self::assertSame($timezones, $pipelineInput->getTimezones());
        self::assertSame($dateTimeFormat, $pipelineInput->getDateTimeFormat());
        self::assertSame($prefix, $pipelineInput->getPrefix());
        self::assertSame($useCallStackOrder, $pipelineInput->getUseCallStackOrder());
        self::assertSame($channel, $pipelineInput->getChannel());
        self::assertSame($level, $pipelineInput->getLevel());
        self::assertSame($message, $pipelineInput->getCallerMessage());
        self::assertSame($exception, $pipelineInput->getException());
        self::assertSame($callerContextArray, $pipelineInput->getCallerContextArray());
        self::assertSame($clarityContext, $pipelineInput->getClarityContext());
        self::assertSame($occurredAt, $pipelineInput->getOccurredAt());



        // some different values
        $runningInConsole = false;
        $consoleCommand = null;
        $useCallStackOrder = false;
        $message = null;
        $exception = null;
        $clarityContext = null;
        $occurredAt = Carbon::now();

        $pipelineInput = new PipelineInput(
            $projectRootDir,
            $runningInConsole,
            $consoleCommand,
            $defaultRenderer,
            $channelRenderers,
            $timezones,
            $dateTimeFormat,
            $prefix,
            $useCallStackOrder,
            $channel,
            $level,
            $message,
            $exception,
            $callerContextArray,
            $clarityContext,
            $occurredAt,
        );

        self::assertSame($runningInConsole, $pipelineInput->getRunningInConsole());
        self::assertSame($consoleCommand, $pipelineInput->getConsoleCommand());
        self::assertSame($useCallStackOrder, $pipelineInput->getUseCallStackOrder());
        self::assertSame($message, $pipelineInput->getCallerMessage());
        self::assertSame($exception, $pipelineInput->getException());
        self::assertSame($clarityContext, $pipelineInput->getClarityContext());
        self::assertSame($occurredAt, $pipelineInput->getOccurredAt());



        // some different values
        $occurredAt = null;

        $pipelineInput = new PipelineInput(
            $projectRootDir,
            $runningInConsole,
            $consoleCommand,
            $defaultRenderer,
            $channelRenderers,
            $timezones,
            $dateTimeFormat,
            $prefix,
            $useCallStackOrder,
            $channel,
            $level,
            $message,
            $exception,
            $callerContextArray,
            $clarityContext,
            $occurredAt,
        );

        self::assertSame($occurredAt, $pipelineInput->getOccurredAt());
    }
}
