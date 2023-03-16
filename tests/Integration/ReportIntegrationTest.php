<?php

namespace CodeDistortion\ClarityLogger\Tests\Integration;

use CodeDistortion\ClarityLogger\Logger;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Test the Report class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class ReportIntegrationTest extends LaravelTestCase
{
    /**
     * Test that *something* is logged when an exception is reported.
     *
     * @test
     *
     * @return void
     */
    public static function test_exception_reporting(): void
    {
        Log::shouldReceive('channel')->withArgs(['stack'])->once()->andReturnSelf();
        Log::shouldReceive(Settings::REPORTING_LEVEL_ERROR)->once()->andReturnSelf();

        $exception = new Exception('Something happened');
        Logger::log($exception);
    }

    /**
     * Test that *something* is logged when the user manually reports something.
     *
     * @test
     * @dataProvider manualLoggingDataProvider
     *
     * @param string $level The reporting level to use.
     * @return void
     */
    public static function test_manual_reporting(string $level): void
    {
        Log::shouldReceive('channel')->withArgs(['stack'])->once()->andReturnSelf();
        Log::shouldReceive($level)->once()->andReturnSelf();

        Logger::$level('hello');
    }

    /**
     * Test that *something* is logged when the user manually reports something.
     *
     * @test
     * @dataProvider manualLoggingDataProvider
     *
     * @param string $level The reporting level to use.
     * @return void
     */
    public static function test_manual_reporting_with_channel(string $level): void
    {
        Log::shouldReceive('channel')->withArgs(['slack'])->once()->andReturnSelf();
        Log::shouldReceive($level)->once()->andReturnSelf();

        Logger::channel('slack')->$level('hello');
    }

    /**
     * DataProvider for test_manual_reporting() and test_manual_reporting_with_channel().
     *
     * @return array<array<string, string>>
     */
    public static function manualLoggingDataProvider(): array
    {
        return [
            ['level' => Settings::REPORTING_LEVEL_DEBUG],
            ['level' => Settings::REPORTING_LEVEL_INFO],
            ['level' => Settings::REPORTING_LEVEL_NOTICE],
            ['level' => Settings::REPORTING_LEVEL_WARNING],
            ['level' => Settings::REPORTING_LEVEL_ERROR],
            ['level' => Settings::REPORTING_LEVEL_CRITICAL],
            ['level' => Settings::REPORTING_LEVEL_ALERT],
            ['level' => Settings::REPORTING_LEVEL_EMERGENCY],
        ];
    }
}
