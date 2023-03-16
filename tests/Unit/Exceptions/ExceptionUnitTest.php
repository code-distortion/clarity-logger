<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Exceptions;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerPipelineException;
use CodeDistortion\ClarityLogger\Output\OutputInterface;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\PipeInterface;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the Exception classes.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class ExceptionUnitTest extends PHPUnitTestCase
{
    /**
     * Test the messages that exceptions generate.
     *
     * @test
     *
     * @return void
     */
    public static function test_exception_messages(): void
    {
        // ClarityLoggerInitialisationException

        self::assertSame(
            'The current framework type could not be resolved',
            ClarityLoggerInitialisationException::unknownFramework()->getMessage()
        );

        self::assertSame(
            "Level \"blah\" is not allowed. Please choose from: " . implode(', ', Settings::LOG_LEVELS),
            ClarityLoggerInitialisationException::levelNotAllowed('blah')->getMessage()
        );



        // ClarityLoggerPipelineException

        self::assertSame(
            "\"not-a-renderer\" does not implement " . RendererInterface::class,
            ClarityLoggerPipelineException::invalidRendererClass('not-a-renderer')->getMessage()
        );

        self::assertSame(
            "\"not-a-pipe\" does not implement " . PipeInterface::class,
            ClarityLoggerPipelineException::invalidPipeClass('not-a-pipe')->getMessage()
        );

        self::assertSame(
            "The method \"method\" on pipe \"pipe-class\" does not exist or is not callable",
            ClarityLoggerPipelineException::pipeMethodNotCallable('pipe-class', 'method')->getMessage()
        );

        self::assertSame(
            "\"not-an-output\" does not implement " . OutputInterface::class,
            ClarityLoggerPipelineException::invalidPipeOutputClass('not-an-output')->getMessage()
        );

        self::assertSame(
            'Multiple response types were given by the pipeline: "array, string"',
            ClarityLoggerPipelineException::multipleResponseTypesGiven(['string', 'array'])->getMessage()
        );

        self::assertSame(
            'An invalid response type was given by the pipeline: integer',
            ClarityLoggerPipelineException::invalidResponseTypeGiven('integer')->getMessage()
        );
    }
}
