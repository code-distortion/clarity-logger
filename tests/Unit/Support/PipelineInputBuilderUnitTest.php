<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Support;

use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Context;
use CodeDistortion\ClarityContext\Support\MetaCallStack;
use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Renderers\Laravel\TextRenderer;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use CodeDistortion\ClarityLogger\Support\InternalSettings;
use CodeDistortion\ClarityLogger\Support\PipelineInputBuilder;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use Exception;
use Throwable;

/**
 * Test the PipelineInputBuilder class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PipelineInputBuilderUnitTest extends LaravelTestCase
{
    /**
     * Test the building of pipeline input objects.
     *
     * @test
     * @dataProvider InputBuilderDataProvider
     *
     * @param boolean               $buildClarityContext    Whether to build a ClarityContext object or not.
     * @param string[]              $clarityContextChannels The channels to pass to the ClarityContext object.
     * @param string|null           $clarityContextLevel    The level to pass to the ClarityContext object.
     * @param string|null           $explicitChannel        The channel specified by the caller.
     * @param string|null           $explicitLevel          The level specified by the caller.
     * @param string|null           $callerMessage          The callerMessage to use.
     * @param Throwable|null        $exception              The exception to use.
     * @param array<string,integer> $callerContextArray     The callerContextArray to use.
     * @param boolean               $expectException        Whether an exception should be thrown.
     * @return void
     * @throws ClarityLoggerInitialisationException When the current framework can't be determined.
     */
    public static function test_building_of_pipeline_input_objects(
        bool $buildClarityContext,
        array $clarityContextChannels,
        ?string $clarityContextLevel,
        ?string $explicitChannel,
        ?string $explicitLevel,
        ?string $callerMessage,
        ?Throwable $exception,
        array $callerContextArray,
        bool $expectException,
    ): void {

        $config = Framework::config();

        // set these default config values so it's clearer when they've been picked up
        $prefix = InternalSettings::LARAVEL_LOGGER__CONFIG_NAME;
        $config->updateConfig([$prefix . '.renderers.channels' => ['slack' => 'slackRenderer']]);
        $config->updateConfig([$prefix . '.time.timezones' => 'Australia/Sydney']);
        $config->updateConfig([$prefix . '.time.format' => 'r']);
        $config->updateConfig([$prefix . '.prefix' => 'blah']);
        $config->updateConfig([$prefix . '.oldest_first' => false]);

        $clarityContext = null;
        if ($buildClarityContext) {

            // stop if the Clarity Context package isn't installed
            // Note: it's ok that this test isn't performed (Github actions runs the tests with and without)
            if (!class_exists(Clarity::class)) {
                self::assertTrue(true);
                return;
            }



            $clarityContext = new Context(
                $exception,
                !$exception ? debug_backtrace() : null,
                new MetaCallStack(),
                1,
                [],
                Framework::config()->getProjectRootDir(),
                $clarityContextChannels,
                $clarityContextLevel,
                true,
                false,
                null,
            );
        }

        $expectedChannel = $explicitChannel
            ?: ($clarityContext?->getChannels()[0] ?? null)
            ?: $config->getFrameworkDefaultChannels()[0];

        $expectedChannels = array_filter([$explicitChannel])
            ?: $clarityContext?->getChannels()
            ?: $config->getFrameworkDefaultChannels();

        if ($exception) {
            $expectedLevel = $explicitLevel ?? $clarityContext?->getLevel() ?? $config->getDefaultExceptionLevel();
        } else {
            $expectedLevel = $explicitLevel ?? $clarityContext?->getLevel() ?? $config->getDefaultMessageLevel();
        }

        $expectedRendererClass = $expectedChannel == 'slack' ? 'slackRenderer' : TextRenderer::class;

        $expectedInputCount = count($expectedChannels) ?: 1;

        $occurredAt = CarbonImmutable::now();



        $inputs = [];
        $exceptionOccurred = false;
        try {
            $builder = new PipelineInputBuilder(
                $explicitChannel,
                $explicitLevel,
                $callerMessage,
                $exception,
                $callerContextArray,
                $clarityContext,
                $occurredAt,
            );

            $inputs = $builder->build();

        } catch (ClarityLoggerInitialisationException $e) {
//            dump("Exception: \"{$e->getMessage()}\" in {$e->getFile()}:{$e->getLine()}");
            $exceptionOccurred = true;
        }



        self::assertSame($expectException, $exceptionOccurred);
        if (!$exceptionOccurred) {

            $input = $inputs[0];
            $runningInConsole = $config->runningInConsole();

            self::assertSame($expectedInputCount, count($inputs));

            self::assertSame($config->getProjectRootDir(), $input->getProjectRootDir());
            self::assertSame($runningInConsole, $input->getRunningInConsole());
            self::assertSame($runningInConsole ? $config->getConsoleCommand() : null, $input->getConsoleCommand());
            self::assertSame($expectedRendererClass, $input->resolveRendererClass($input->getChannel()));
            self::assertSame($config->getTimezones(), $input->getTimezones());
            self::assertSame($config->getDateTimeFormat(), $input->getDateTimeFormat());
            self::assertSame($config->getPrefix(), $input->getPrefix());
            self::assertSame($config->getUseCallStackOrder(), $input->getUseCallStackOrder());
            self::assertSame($expectedChannel, $input->getChannel());
            self::assertSame($expectedLevel, $input->getLevel());
            self::assertSame($callerMessage, $input->getCallerMessage());
            self::assertSame($exception, $input->getException());
            self::assertSame($callerContextArray, $input->getCallerContextArray());
            self::assertSame($clarityContext, $input->getClarityContext());
            self::assertSame($occurredAt, $input->getOccurredAt());
        }
    }

    /**
     * DataProvider for test_building_of_pipeline_input_objects().
     *
     * @return array<array<string,string,Exception,array<string,integer>,string,Context,CarbonImmutable,integer,string,boolean>>
     */
    public static function InputBuilderDataProvider(): array
    {
        $possibleExplicitChannels = [
            null,
            'slack',
        ];

        $possibleClarityContextChannels = [
            [],
            ['slack'],
            ['stack', 'slack'],
        ];

        $possibleLevels = [
            null,
            Settings::REPORTING_LEVEL_DEBUG,
            'invalid',
        ];

        $possibleCallerContextArrays = [
            [],
            ['abc' => 123],
        ];



        $return = [];

        foreach ([true, false] as $buildClarityContext) {

            foreach ($possibleClarityContextChannels as $clarityContextChannels) {
                foreach ($possibleLevels as $clarityContextLevel) {

                    // don't worry about multiple channels if there won't be a Clarity Context object
                    if (!$buildClarityContext) {
                        if (count($clarityContextChannels)) {
                            continue;
                        }
                        if (!is_null($clarityContextLevel)) {
                            continue;
                        }
                    }

                    foreach ($possibleExplicitChannels as $explicitChannel) {
                        foreach ($possibleLevels as $explicitLevel) {

                            foreach ([null, new Exception()] as $exception) {

                                foreach ($possibleCallerContextArrays as $callerContextArray) {

                                    $expectException = false;
                                    if (is_string($explicitLevel)) {
                                        $expectException = !in_array($explicitLevel, Settings::LOG_LEVELS);
                                    } elseif (is_string($clarityContextLevel)) {
                                        $expectException = !in_array($clarityContextLevel, Settings::LOG_LEVELS);
                                    }

                                    $return[] = [
                                        'buildClarityContext' => $buildClarityContext,
                                        'clarityContextChannels' => $clarityContextChannels,
                                        'clarityContextLevel' => $clarityContextLevel,
                                        'explicitChannel' => $explicitChannel,
                                        'explicitLevel' => $explicitLevel,
                                        'callerMessage' => $exception ? null : 'some message',
                                        'exception' => $exception,
                                        'callerContextArray' => $callerContextArray,
                                        'expectException' => $expectException,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }
}
