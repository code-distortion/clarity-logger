<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Support;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Helpers\FileHelper;
use CodeDistortion\ClarityLogger\Helpers\MethodHelper;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Support;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the Support class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class SupportUnitTest extends PHPUnitTestCase
{
    /**
     * Test that the method that ensures a level is valid will throw an exception when the level isn't valid.
     *
     * @test
     * @dataProvider ensureLevelIsValidDataProvider
     *
     * @param string  $level           The level to test.
     * @param boolean $expectException Whether an exception should be thrown or not.
     * @return void
     */
    public static function test_the_method_that_ensures_a_level_is_valid(string $level, bool $expectException): void
    {
        $exceptionWasThrown = false;
        try {
            Support::ensureLevelIsValid($level);
        } catch (ClarityLoggerInitialisationException) {
            $exceptionWasThrown = true;
        }

        self::assertSame($expectException, $exceptionWasThrown);
    }

    /**
     * DataProvider for test_the_method_that_ensures_a_level_is_valid().
     *
     * @return array<array<string, string|boolean>>
     */
    public static function ensureLevelIsValidDataProvider(): array
    {
        $return = [];

        foreach (Settings::LOG_LEVELS as $level) {
            $return[] = [
                'level' => $level,
                'expectException' => false,
            ];
        }

        $return[] = [
            'level' => 'invalid',
            'expectException' => true,
        ];

        return $return;
    }





    /**
     * Test the method that prepares a stack trace.
     *
     * @test
     *
     * @return void
     */
    public static function test_prepare_stack_trace_method(): void
    {
        $phpStackTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
        $preparedStackTrace = Support::preparePHPStackTrace($phpStackTrace);



        // check the function of the earliest frame
        $lastKey = array_key_last($preparedStackTrace);
        self::assertSame('[top]', $preparedStackTrace[$lastKey]['function']);



        // test that at least the files and lines are correct
        // test that the functions are shifted by one frame

        // build a representation of the frames based on PHP's stack trace
        $phpCallstackFrames = [];
        $function = '[top]';
        foreach (array_reverse($phpStackTrace) as $frame) {
            $phpCallstackFrames[] = [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'function' => $function,
            ];
            $function = $frame['function']; // shift the function by 1 frame
        }
        $phpStackTraceFrames = array_reverse($phpCallstackFrames);

        // build a representation of the frames based on the prepared stack trace
        $preparedStackTraceFrames = [];
        foreach ($preparedStackTrace as $frame) {
            $preparedStackTraceFrames[] = [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'function' => $frame['function'] ?? null,
            ];
        }

        self::assertSame($phpStackTraceFrames, $preparedStackTraceFrames);



        // check that the 'object' field in each frame has been turned into its spl_object_id (i.e. an integer)
        foreach ($preparedStackTrace as $frameData) {
            self::assertTrue(is_int($frameData['object'] ?? -1));
        }



        // check that the extra frame added by call_user_func_array() (that's missing its file and line) is removed
        $pretendStackTrace = [
            [
                'file' => '', // <<<
                'line' => 0, // <<<
            ],
            [
                'file' => 'file2',
                'line' => 124,
            ],
            [
                'file' => 'file1',
                'line' => 123,
            ],
        ];
        $preparedStackTrace = Support::preparePHPStackTrace($pretendStackTrace);
        self::assertCount(2, $preparedStackTrace);
        self::assertSame('file2', $preparedStackTrace[0]['file']);
        self::assertSame(124, $preparedStackTrace[0]['line']);
    }





    /**
     * Test that Laravel exception handler frames can be removed.
     *
     * @test
     * @dataProvider exceptionHandlerFramesDataProvider
     *
     * @param integer $startFrameCount The number of frames to start with.
     * @param integer $addFrames       The number of frames to add.
     * @return void
     */
    public static function test_that_laravel_exception_handler_frames_are_pruned(int $startFrameCount, int $addFrames)
    {
        $phpStackTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
        $stackTrace = Support::preparePHPStackTrace($phpStackTrace, __FILE__, __LINE__);
        $stackTrace = array_slice($stackTrace, 0, $startFrameCount);
        $stackTrace = self::addLaravelExceptionHandlerFrames($stackTrace, $addFrames);

        $origCount = count($stackTrace);
        $stackTrace = Support::pruneLaravelExceptionHandlerFrames($stackTrace);

        self::assertCount($origCount - $addFrames, $stackTrace);
    }

    /**
     * DataProvider for test_that_laravel_exception_handler_frames_are_pruned().
     *
     * @return array<array<string,integer>>
     */
    public static function exceptionHandlerFramesDataProvider(): array
    {
        return [
            ['startFrameCount' => 5, 'addFrames' => 0],
            ['startFrameCount' => 5, 'addFrames' => 1],
            ['startFrameCount' => 5, 'addFrames' => 2],

            ['startFrameCount' => 1, 'addFrames' => 0],
            ['startFrameCount' => 1, 'addFrames' => 1],
            ['startFrameCount' => 1, 'addFrames' => 2],

            ['startFrameCount' => 0, 'addFrames' => 0],
            ['startFrameCount' => 0, 'addFrames' => 1],
            ['startFrameCount' => 0, 'addFrames' => 2],
        ];
    }

    /**
     * Add some Laravel exception handler frames to a stack trace.
     *
     * @param array<integer, mixed[]> $stackTrace The stack trace to add the frames to.
     * @param integer                 $addFrames  The number of frames to add.
     * @return array<integer, mixed[]>
     */
    private static function addLaravelExceptionHandlerFrames(array $stackTrace, int $addFrames): array
    {
        $newFrames = [
            [
                'file' => '/var/www/html/vendor/'
                    . 'laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php',
                'line' => 254,
                'function' => 'Illuminate\Foundation\Bootstrap\{closure}',
                'class' => 'Illuminate\Foundation\Bootstrap\HandleExceptions',
                'type' => '->',
            ],
            [
                'file' => '/var/www/html/routes/web.php',
                'line' => 51,
                'function' => 'handleError',
                'class' => 'Illuminate\Foundation\Bootstrap\HandleExceptions',
                'type' => '->',
            ],
        ];

        $newFrames = array_slice($newFrames, 0, $addFrames);

        return array_merge($newFrames, $stackTrace);
    }
}
