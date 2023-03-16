<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Helpers;

use CodeDistortion\ClarityLogger\Helpers\CommandLineHelper;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the CommandLineHelper class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class CommandLineHelperUnitTest extends PHPUnitTestCase
{
    /**
     * Test that the command line arguments are escaped properly.
     *
     * @test
     * @dataProvider CommandLineArgsDataProvider
     *
     * @param string[] $args            The arguments to escape (e.g. from $_SERVER['argv']).
     * @param string   $expected        The expected result.
     * @param string   $expectedWindows The expected result, when running Windows.
     * @return void
     */
    public static function test_command_line_arg_escaping(array $args, string $expected, string $expectedWindows): void
    {
        $runningInWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        self::assertSame(
            $runningInWindows ? $expectedWindows : $expected,
            CommandLineHelper::renderCommandLine($args)
        );
    }

    /**
     * DataProvider for test_command_line_arg_escaping().
     *
     * @return array<array<string,array<string>|string>>
     */
    public static function CommandLineArgsDataProvider(): array
    {
        $return = [];

        // empty
        $return[] = [
            'args' => [],
            'expected' => '',
            'expectedWindows' => '',
        ];

        // one argument
        $return[] = [
            'args' => ['a'],
            'expected' => 'a',
            'expectedWindows' => 'a',
        ];

        // an empty argument
        $return[] = [
            'args' => [''],
            'expected' => "''",
            'expectedWindows' => '""',
        ];

        // several args
        $return[] = [
            'args' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'],
            'expected' => 'a b c d e f g h i',
            'expectedWindows' => 'a b c d e f g h i',
        ];

        // an arg with a single quote
        $return[] = [
            'args' => ['a', '\'b'],
            'expected' => "a ''\''b'",
            'expectedWindows' => "a 'b",
        ];

        // an arg with a double quote
        $return[] = [
            'args' => ['a', '"b', 'c'],
            'expected' => 'a "b c',
            'expectedWindows' => 'a " b" c',
        ];

        // an arg with spaces
        $return[] = [
            'args' => ['a', ' b ', 'c'],
            'expected' => "a ' b ' c",
            'expectedWindows' => 'a " b " c',
        ];

        // an arg with tabs
        $return[] = [
            'args' => [" \ta ", 'b', 'c'],
            'expected' => "' \ta ' b c",
            'expectedWindows' => "\" \ta \" b c",
        ];

        // an arg with new lines
        $return[] = [
            'args' => ['a', 'b', "\nc"],
            'expected' => "a b \nc",
            'expectedWindows' => "a b \nc",
        ];

        // an arg with a slash
        $return[] = [
            'args' => ['\\a', 'b', 'c'],
            'expected' => "\\a b c",
            'expectedWindows' => "\\a b c",
        ];

        // an arg with a slash
        $return[] = [
            'args' => ['\\a', 'b', 'c'],
            'expected' => "\\a b c",
            'expectedWindows' => "\\a b c",
        ];

        return $return;
    }
}
