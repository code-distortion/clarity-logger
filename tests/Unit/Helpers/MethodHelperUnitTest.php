<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Helpers;

use CodeDistortion\ClarityLogger\Helpers\MethodHelper;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the MethodHelper class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class MethodHelperUnitTest extends PHPUnitTestCase
{
    /**
     * Test the method that resolves the current method.
     *
     * @test
     * @dataProvider resolveCurrentMethodDataProvider
     *
     * @param string|null $class    The class being used.
     * @param string|null $function The function being used.
     * @param string      $expected The expected output.
     * @return void
     */
    public static function test_resolve_current_method_method(?string $class, ?string $function, string $expected): void
    {
        self::assertSame($expected, MethodHelper::resolveCurrentMethod($class, $function));
    }

    /**
     * DataProvider for test_resolve_current_method_method().
     *
     * @return array<array<string, string|null>>
     */
    public static function resolveCurrentMethodDataProvider(): array
    {
        $return = [];

        $return[] = [
            'class' => null,
            'function' => null,
            'expected' => '',
        ];

        $return[] = [
            'class' => 'abc',
            'function' => null,
            'expected' => '',
        ];

        $return[] = [
            'class' => 'abc',
            'function' => 'xyz{closure}',
            'expected' => 'closure',
        ];

        $return[] = [
            'class' => null,
            'function' => 'xyz',
            'expected' => 'function "xyz"',
        ];

        $return[] = [
            'class' => 'abc',
            'function' => 'xyz',
            'expected' => 'method "xyz"',
        ];

        return $return;
    }
}
