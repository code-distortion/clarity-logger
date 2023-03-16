<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Helpers;

use CodeDistortion\ClarityLogger\Helpers\PrefixHelper;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the PrefixHelper class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PrefixHelperUnitTest extends PHPUnitTestCase
{
    /**
     * Test that prefixes are added properly.
     *
     * @test
     * @dataProvider prefixDataProvider
     *
     * @param string $prefix   The prefix to add.
     * @param string $lines    The lines to add the prefix to.
     * @param string $expected The expected result.
     * @return void
     */
    public static function test_that_a_prefix_is_added_properly(string $prefix, string $lines, string $expected): void
    {
        self::assertSame($expected, PrefixHelper::add($prefix, $lines));
    }

    /**
     * DataProvider for test_that_a_prefix_is_added_properly().
     *
     * @return array<array<string, string>>
     */
    public static function prefixDataProvider(): array
    {
        $return = [];

        $return[] = [
            'prefix' => '',
            'lines' => '',
            'expected' => '',
        ];



        // single line
        $return[] = [
            'prefix' => '',
            'lines' => 'hello',
            'expected' => 'hello',
        ];

        $return[] = [
            'prefix' => 'ABC ',
            'lines' => 'hello',
            'expected' => 'ABC hello',
        ];



        // multiple lines
        $return[] = [
            'prefix' => '',
            'lines' => "hello" . PHP_EOL . "world",
            'expected' => "hello" . PHP_EOL . "world",
        ];

        $return[] = [
            'prefix' => 'ABC ',
            'lines' => "hello" . PHP_EOL . "world",
            'expected' => "ABC hello" . PHP_EOL . "ABC world",
        ];



        // multiple lines, with an empty line
        $return[] = [
            'prefix' => '',
            'lines' => "hello" . PHP_EOL . '' . PHP_EOL . "world",
            'expected' => "hello" . PHP_EOL . '' . PHP_EOL . "world",
        ];

        $return[] = [
            'prefix' => 'ABC ',
            'lines' => "hello" . PHP_EOL . '' . PHP_EOL . "world",
            'expected' => "ABC hello" . PHP_EOL . 'ABC' . PHP_EOL . "ABC world",
        ];



        // test with empty first and last lines
        $return[] = [
            'prefix' => 'ABC ',
            'lines' => '' . PHP_EOL . 'hello' . PHP_EOL . '',
            'expected' => 'ABC' . PHP_EOL . 'ABC hello' . PHP_EOL . 'ABC',
        ];



        // test the right-trimming of the prefix
        $return[] = [
            'prefix' => ' ABC ',
            'lines' => "hello" . PHP_EOL . '' . PHP_EOL . "world",
            'expected' => " ABC hello" . PHP_EOL . ' ABC' . PHP_EOL . " ABC world",
        ];

        return $return;
    }
}
