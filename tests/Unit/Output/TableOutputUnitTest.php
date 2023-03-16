<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Output;

use CodeDistortion\ClarityLogger\Output\TableOutput;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the TableOutput class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class TableOutputUnitTest extends PHPUnitTestCase
{
    /**
     * Test that the TableOutput class accepts various inputs, and renders the output properly.
     *
     * @test
     * @dataProvider tablePopulationAndRenderingDataProvider
     *
     * @param array<string,array<string|mixed[]|null>|null> $rows     The rows to add.
     * @param string                                        $expected The expected output.
     * @return void
     */
    public static function test_table_output_population_and_rendering(array $rows, string $expected): void
    {
        $output = new TableOutput();

        foreach ($rows as $row) {

            if (is_null($row)) {
                $output = $output->blankRow(); // test chaining too
                continue;
            }

            /** @var string $title */
            $title = $row[0];
            $output = $output->row($title, $row[1]); // test chaining too
        }

        self::assertSame($expected, $output->render());
    }

    /**
     * DataProvider for test_table_output_population_and_rendering().
     *
     * @return array<int,array<string,array<string|mixed[]|null>|null>|string>
     */
    public static function tablePopulationAndRenderingDataProvider(): array
    {
        $return = [];

        // no rows
        $return[] = [
            'rows' => [],
            'expected' => '',
        ];

        // single row with a null value
        $return[] = [
            'rows' => [['Hello', null]],
            'expected' => '',
        ];

        // single string row
        $return[] = [
            'rows' => [['Hello', 'there']],
            'expected' => 'Hello  there',
        ];

        // multiple string rows
        $return[] = [
            'rows' => [
                ['Hello1', 'there1'],
                ['Hello2', 'there2'],
            ],
            'expected' => 'Hello1  there1' . PHP_EOL
                        . 'Hello2  there2',
        ];

        // with an empty string row
        $return[] = [
            'rows' => [
                ['Hello1', 'there1'],
                ['Hello2', ''],
                ['Hello3', 'there3'],
            ],
            'expected' => 'Hello1  there1' . PHP_EOL
                . 'Hello3  there3',
        ];

        // rows with different length titles
        $return[] = [
            'rows' => [
                ['short', 'a'],
                ['longggg', 'b'],
            ],
            'expected' => 'short    a' . PHP_EOL
                        . 'longggg  b',
        ];

        // same titles
        $return[] = [
            'rows' => [
                ['string', 'there1'],
                ['string', 'there2'],
            ],
            'expected' => 'string  there1' . PHP_EOL
                        . 'string  there2',
        ];

        // with a blank title
        $return[] = [
            'rows' => [
                ['string', 'there1'],
                ['', 'there2'],
                ['string', 'there3'],
            ],
            'expected' => 'string  there1' . PHP_EOL
                . '        there2' . PHP_EOL
                . 'string  there3',
        ];

        // a row with an empty array value
        $return[] = [
            'rows' => [
                ['array', []],
            ],
            'expected' => "",
        ];

        // a row with an array value
        $return[] = [
            'rows' => [
                ['array', ['a']],
            ],
            'expected' => "array  0 = 'a'",
        ];

        // a row with a more complex array value
        $return[] = [
            'rows' => [
                ['array', ['id1' => 1, 'id2' => 2]],
            ],
            'expected' => "array  id1 = 1" . PHP_EOL
                        . '       id2 = 2',
        ];

        // a row with a nested array value
        $return[] = [
            'rows' => [
                ['array', [['id1' => 1, 'id2' => 2]]],
            ],
            'expected' => "array  0 = [" . PHP_EOL
                        . "         'id1' => 1," . PHP_EOL
                        . "         'id2' => 2," . PHP_EOL
                        . "       ]",
        ];

        // a row with an array with integer keys
        $return[] = [
            'rows' => [
                ['array', [1, 2]],
            ],
            'expected' => "array  0 = 1" . PHP_EOL
                        . "       1 = 2",
        ];


        // string and array rows
        $return[] = [
            'rows' => [
                ['string', 'a'],
                ['array', [['id1' => 1, 'id2' => 2]]],
                ['string', 'b'],
            ],
            'expected' => "string  a" . PHP_EOL
                        . "array   0 = [" . PHP_EOL
                        . "          'id1' => 1," . PHP_EOL
                        . "          'id2' => 2," . PHP_EOL
                        . "        ]" . PHP_EOL
                        . "string  b",
        ];

        // only one blank row
        $return[] = [
            'rows' => [null],
            'expected' => "",
        ];

        // only blank rows
        $return[] = [
            'rows' => [null, null],
            'expected' => "" . PHP_EOL . "",
        ];

        // a blank row in the middle
        $return[] = [
            'rows' => [
                ['string', 'a'],
                null,
                ['string', 'b'],
            ],
            'expected' => "string  a" . PHP_EOL
                . "" . PHP_EOL
                . "string  b",
        ];

        return $return;
    }
}
