<?php

namespace CodeDistortion\ClarityLogger\Tests\Integration;

use Carbon\CarbonImmutable;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use CodeDistortion\ClarityLogger\Tests\Support\RendererWithInternalException;
use CodeDistortion\ClarityLogger\Tests\Support\RendererWithSwallowedInternalExceptions;
use Illuminate\Contracts\Foundation\Application;

/**
 * Test the TextRenderer pipeline when exceptions are generated.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PipelineExceptionIntegrationTest extends LaravelTestCase
{
    /**
     * Test a pipeline that generates internal exceptions.
     *
     * @test
     *
     * @return void
     */
    public static function test_pipeline_exception(): void
    {
        $channel = 'stack';
        $input = self::buildInput($channel, RendererWithInternalException::class);
        $renderer = self::buildRenderer($input->resolveRendererClass($channel));
        $output = $renderer->render($input);

        $expectedOutput = "--------" . PHP_EOL
                . "--------" . PHP_EOL
                . PHP_EOL
                . "EXCEPTIONS (that occurred when building the report)" . PHP_EOL
                . PHP_EOL
                . "exception 1  CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerPipelineException: \""
                    . "\"CodeDistortion\ClarityLogger\Tests\Support\Pipes\TriggerExceptionDuringInstantiationPipe\" "
                    . "does not implement CodeDistortion\ClarityLogger\Pipelines\Pipes\PipeInterface"
                    . "\"" . PHP_EOL
                . "- location   src/Exceptions/ClarityLoggerPipelineException.php on line 33 "
                    . "(method \"invalidPipeClass\")" . PHP_EOL
                . PHP_EOL
                . "exception 2  Exception: \"Something happened\"" . PHP_EOL
                . "- location   tests/Support/Pipes/TriggerExceptionDuringRunPipe.php on line 21 "
                    . "(method \"run\")" . PHP_EOL
                . PHP_EOL
                . "--------";

        self::assertSame($expectedOutput, $output);
    }


    /**
     * Test a pipeline that generates internal exceptions.
     *
     * @test
     *
     * @return void
     */
    public static function test_swallowed_pipeline_exceptions(): void
    {
        $channel = 'stack';
        $input = self::buildInput($channel, RendererWithSwallowedInternalExceptions::class);
        $renderer = self::buildRenderer($input->resolveRendererClass($channel));
        $output = $renderer->render($input);

        $expectedOutput = "--------" . PHP_EOL
            . "--------" . PHP_EOL
            . PHP_EOL
            . "--------";

        self::assertSame($expectedOutput, $output);
    }



    /**
     * Build a new renderer instance.
     *
     * @param class-string $rendererClass The class to use.
     * @return RendererInterface
     *
     */
    private static function buildRenderer(string $rendererClass): RendererInterface
    {
        /** @var Application $app */
        $app = app();

        /** @var RendererInterface $renderer */
        $renderer = $app->make($rendererClass);

        return $renderer;
    }

    /**
     * Build a PipelineInput object.
     *
     * @param string       $channel         The channel to use.
     * @param class-string $defaultRenderer The default-renderer class to use.
     * @return PipelineInput
     */
    private static function buildInput(string $channel, string $defaultRenderer): PipelineInput
    {
        return new PipelineInput(
            Framework::config()->getProjectRoot(),
            true,
            'test command',
            $defaultRenderer,
            [],
            ['UTC'],
            ['r'],
            '',
            true, // todo - check if this needs to be tested more here
            $channel,
            Settings::REPORTING_LEVEL_DEBUG,
            'hello',
            null,
            [],
            null,
            CarbonImmutable::createFromTimestamp(time(), 'UTC'),
        );
    }
}
