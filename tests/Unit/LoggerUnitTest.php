<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit;

use CodeDistortion\ClarityLogger\Logger;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Test the Logger class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class LoggerUnitTest extends LaravelTestCase
{
    /**
     *
     * Test that Logger can chain channel(..), level(..) and log(..) methods (note: passing $toLog to the log method),
     * and that the level and channel used are correct.
     *
     * @test
     * @dataProvider LoggerDataProvider
     *
     * @param string|null                $channel         The channel to use.
     * @param string                     $expectedChannel The expected channel to be used.
     * @param string|null                $level           The level to use.
     * @param string                     $expectedLevel   The expected level to be used.
     * @param string|Exception           $toLog           The string or exception to log.
     * @param array<string,integer>|null $context         The context to log.
     * @return void
     */
    public static function test_logger_chaining(
        ?string $channel,
        string $expectedChannel,
        ?string $level,
        string $expectedLevel,
        string|Exception $toLog,
        ?array $context,
    ): void {

        $argCheck = $context
            ? fn($level2, $output) => ($level2 === $expectedLevel) && (mb_strpos($output, 'some-id = 123') !== false)
            : fn($level2, $output) => ($level2 === $expectedLevel) && (mb_strpos($output, 'some-id = 123') === false);

        Log::shouldReceive('channel')->withArgs([$expectedChannel])->once()->andReturnSelf();
        Log::shouldReceive('log')->withArgs($argCheck)->once()->andReturnSelf();

        // with channel and level
        if (($channel) && ($level)) {

            (!is_null($context))
                ? Logger::channel($channel)->level($level)->log($toLog, $context)
                : Logger::channel($channel)->level($level)->log($toLog);

        // with channel only
        } elseif ($channel) {

            (!is_null($context))
                ? Logger::channel($channel)->log($toLog, $context)
                : Logger::channel($channel)->log($toLog);

        // with level only
        } elseif ($level) {

            (!is_null($context))
                ? Logger::level($level)->log($toLog, $context)
                : Logger::level($level)->log($toLog);

        // no channel or level
        } else {

            (!is_null($context))
                ? Logger::log($toLog, $context)
                : Logger::log($toLog);

        }
    }

    /**
     * Test that Logger can chain channel(..), $level() and log(..) methods (note: passing $toLog to the log method, and
     * calling the particular level method, e.g. ->debug()), and that the level and channel used are correct.
     *
     * @test
     * @dataProvider LoggerDataProvider
     *
     * @param string|null                $channel         The channel to use.
     * @param string                     $expectedChannel The expected channel to be used.
     * @param string|null                $level           The level to use.
     * @param string                     $expectedLevel   The expected level to be used.
     * @param string|Exception           $toLog           The string or exception to log.
     * @param array<string,integer>|null $context         The context to log.
     * @return void
     */
    public static function test_logger_chaining_with_particular_level_method_called(
        ?string $channel,
        string $expectedChannel,
        ?string $level,
        string $expectedLevel,
        string|Exception $toLog,
        ?array $context,
    ): void {

        $argCheck = $context
            ? fn($level2, $output) => ($level2 === $expectedLevel) && (mb_strpos($output, 'some-id = 123') !== false)
            : fn($level2, $output) => ($level2 === $expectedLevel) && (mb_strpos($output, 'some-id = 123') === false);

        Log::shouldReceive('channel')->withArgs([$expectedChannel])->once()->andReturnSelf();
        Log::shouldReceive('log')->withArgs($argCheck)->once()->andReturnSelf();

        // with channel and level
        if (($channel) && ($level)) {

            (!is_null($context))
                ? Logger::channel($channel)->$level()->log($toLog, $context)
                : Logger::channel($channel)->$level()->log($toLog);

            // with channel only
        } elseif ($channel) {

            (!is_null($context))
                ? Logger::channel($channel)->log($toLog, $context)
                : Logger::channel($channel)->log($toLog);

            // with level only
        } elseif ($level) {

            (!is_null($context))
                ? Logger::$level()->log($toLog, $context)
                : Logger::$level()->log($toLog);

            // no channel or level
        } else {

            (!is_null($context))
                ? Logger::log($toLog, $context)
                : Logger::log($toLog);

        }
    }

    /**
     * DataProvider for test_logger_chaining() and test_logger_chaining_with_particular_level_method_called().
     *
     * @return array<array<string,string|exception|array<string, integer>|null>>
     */
    public static function LoggerDataProvider(): array
    {
        $return = [];

        foreach (['slack', null] as $channel) {
            foreach ([...Settings::LOG_LEVELS, null] as $level) {
                foreach (['some string', new Exception()] as $toLog) {
                    foreach ([[], ['some-id' => 123], null] as $context) {

                        if ($level) {
                            $expectedLevel = $level;
                        } else {
                            $expectedLevel = $toLog instanceof Exception
                                ? Settings::REPORTING_LEVEL_ERROR
                                : Settings::REPORTING_LEVEL_INFO;
                        }

                        $return[] = [
                            'channel' => $channel,
                            'expectedChannel' => $channel ?? 'stack',
                            'level' => $level,
                            'expectedLevel' => $expectedLevel,
                            'toLog' => $toLog,
                            'context' => $context,
                        ];
                    }
                }
            }
        }

        return $return;
    }





    /**
     * Test that Logger can chain channel(..) and $level($toLog) methods (note: passes $toLog to the level method), and
     * that the level and channel used are correct.
     *
     * @test
     * @dataProvider LoggerDataProvider2
     *
     * @param string|null                $channel         The channel to use.
     * @param string                     $expectedChannel The expected channel to be used.
     * @param string|null                $level           The level to use.
     * @param string                     $expectedLevel   The expected level to be used.
     * @param string|Exception           $toLog           The string or exception to log.
     * @param array<string,integer>|null $context         The context to log.
     * @return void
     */
    public function test_logger_chaining_pass_to_log_to_the_level_call(
        ?string $channel,
        string $expectedChannel,
        ?string $level,
        string $expectedLevel,
        string|Exception $toLog,
        ?array $context,
    ): void {

        $argCheck = $context
            ? fn($level2, $output) => ($level2 === $expectedLevel) && (mb_strpos($output, 'some-id = 123') !== false)
            : fn($level2, $output) => ($level2 === $expectedLevel) && (mb_strpos($output, 'some-id = 123') === false);

        Log::shouldReceive('channel')->withArgs([$expectedChannel])->once()->andReturnSelf();
        Log::shouldReceive('log')->withArgs($argCheck)->once()->andReturnSelf();

        if (($channel) && ($level)) {

            (!is_null($context))
                ? Logger::channel($channel)->$level($toLog, $context)
                : Logger::channel($channel)->$level($toLog);

        } else {

            (!is_null($context))
                ? Logger::$level($toLog, $context)
                : Logger::$level($toLog);
        }
    }

    /**
     * DataProvider for test_logger_chaining_pass_to_log_to_the_level_call().
     *
     * @return array<array<string,string|exception|array<string, integer>|null>>
     */
    public static function LoggerDataProvider2(): array
    {
        $return = [];

        foreach (['slack', null] as $channel) {
            foreach (Settings::LOG_LEVELS as $level) {
                foreach (['some string', new Exception()] as $toLog) {
                    foreach ([['some-id' => 123], [], null] as $context) {

                        $return[] = [
                            'channel' => $channel,
                            'expectedChannel' => $channel ?? 'stack',
                            'level' => $level,
                            'expectedLevel' => $level,
                            'toLog' => $toLog,
                            'context' => $context,
                        ];
                    }
                }
            }
        }

        return $return;
    }
}
