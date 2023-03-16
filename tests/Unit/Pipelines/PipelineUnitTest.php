<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Pipelines;

use CodeDistortion\ClarityLogger\Pipelines\Pipeline;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes\JustALinePipe;
use CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes\TriggerExceptionDuringRunPipe;

/**
 * Test the Pipeline class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PipelineUnitTest extends LaravelTestCase
{
    /**
     * Test that the pipeline doesn't record an exception.
     *
     * @return void
     */
    public static function test_pipeline(): void
    {
        $exceptions = (new Pipeline())
            ->send(['abc' => 'def'])
            ->through([JustALinePipe::class])
            ->go('run')
            ->getExceptions();

        self::assertCount(0, $exceptions);
    }

    /**
     * Test that the pipeline records an exception when the pipe throws an exception.
     *
     * @return void
     */
    public static function test_pipeline_with_pipe_that_throws_exception(): void
    {
        $exceptions = (new Pipeline())
            ->send(['abc' => 'def'])
            ->through([TriggerExceptionDuringRunPipe::class])
            ->go('run')
            ->getExceptions();

        self::assertCount(1, $exceptions);
    }

    /**
     * Test that the pipeline records an exception when given a non Pipe class.
     *
     * @return void
     */
    public static function test_pipeline_when_using_a_non_pipe(): void
    {
        $exceptions = (new Pipeline())->send(['abc' => 'def'])->through([self::class])->getExceptions();
        self::assertCount(1, $exceptions);
    }

    /**
     * Test that the pipeline records an exception when it calls a method that doesn't exist.
     *
     * @return void
     */
    public static function test_pipeline_that_calls_an_invalid_method(): void
    {
        $exceptions = (new Pipeline())
            ->send(['abc' => 'def'])
            ->through([TriggerExceptionDuringRunPipe::class])
            ->go('methodDoesntExist')
            ->getExceptions();

        self::assertCount(1, $exceptions);
    }
}
