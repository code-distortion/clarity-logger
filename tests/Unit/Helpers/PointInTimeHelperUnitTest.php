<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Helpers;

use Carbon\CarbonImmutable;
use CodeDistortion\ClarityLogger\Helpers\PointInTimeHelper;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the PointInTimeHelper class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PointInTimeHelperUnitTest extends PHPUnitTestCase
{
    /**
     * Test the way the point in time is rendered.
     *
     * @test
     * @dataProvider pointsInTimeDataProvider
     *
     * @param CarbonImmutable $carbon   The point in time to render.
     * @param string[]|string $format   The Carbon formatting to use.
     * @param string[]|string $timezone The timezone/s to render.
     * @param string          $expected The expected result.
     * @return void
     */
    public static function test_point_in_time_renders(
        CarbonImmutable $carbon,
        array|string $format,
        array|string $timezone,
        string $expected,
    ): void {

        $pointInTime = new PointInTimeHelper($carbon);
        self::assertSame($expected, $pointInTime->renderAsString($format, $timezone));
    }

    /**
     * DataProvider for test_point_in_time_renders().
     *
     * @return array<array<string,CarbonImmutable|array|string>>
     */
    public static function pointsInTimeDataProvider(): array
    {
        $carbon1 = new CarbonImmutable('2020-01-01 12:34:56', 'UTC');
        $carbon2 = new CarbonImmutable('2020-01-01 12:34:56', 'Australia/Sydney');

        $return = [];

        // a single format, a single timezone
        $return[] = [
            'carbon' => $carbon1,
            'format' => 'r',
            'timezone' => 'UTC',
            'expected' => 'Wed, 01 Jan 2020 12:34:56 +0000',
        ];

        // multiple format parts, a single timezone
        $return[] = [
            'carbon' => $carbon1,
            'format' => ['Y-m-d H:i:s', '', 'r'],
            'timezone' => 'UTC',
            'expected' => '2020-01-01 12:34:56  Wed, 01 Jan 2020 12:34:56 +0000',
        ];

        // a single format, a multiple timezones
        $return[] = [
            'carbon' => $carbon1,
            'format' => 'r',
            'timezone' => ['UTC', 'Australia/Sydney'],
            'expected' => 'Wed, 01 Jan 2020 12:34:56 +0000' . PHP_EOL . 'Wed, 01 Jan 2020 23:34:56 +1100',
        ];

        // multiple format parts, a multiple timezones
        $return[] = [
            'carbon' => $carbon1,
            'format' => ['Y-m-d H:i:s', '', 'r'],
            'timezone' => ['UTC', 'Australia/Sydney'],
            'expected' => '2020-01-01 12:34:56  Wed, 01 Jan 2020 12:34:56 +0000' . PHP_EOL
                . '2020-01-01 23:34:56  Wed, 01 Jan 2020 23:34:56 +1100',
        ];

        // multiple format parts, a multiple timezones (different order)
        $return[] = [
            'carbon' => $carbon1,
            'format' => ['Y-m-d H:i:s', '', 'r'],
            'timezone' => ['Australia/Sydney', 'UTC', 'Asia/Manila'],
            'expected' => '2020-01-01 23:34:56  Wed, 01 Jan 2020 23:34:56 +1100' . PHP_EOL
                . '2020-01-01 12:34:56  Wed, 01 Jan 2020 12:34:56 +0000' . PHP_EOL
                . '2020-01-01 20:34:56  Wed, 01 Jan 2020 20:34:56 +0800',
        ];

        // multiple format parts, a multiple timezones (different source carbon timezone)
        $return[] = [
            'carbon' => $carbon2,
            'format' => ['Y-m-d H:i:s', '', 'r'],
            'timezone' => ['UTC', 'Australia/Sydney'],
            'expected' => '2020-01-01 01:34:56  Wed, 01 Jan 2020 01:34:56 +0000' . PHP_EOL
                . '2020-01-01 12:34:56  Wed, 01 Jan 2020 12:34:56 +1100',
        ];

        return $return;
    }
}
