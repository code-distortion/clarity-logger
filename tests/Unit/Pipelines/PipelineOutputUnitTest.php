<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Pipelines;

use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;

/**
 * Test the PipelineOutput class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PipelineOutputUnitTest extends LaravelTestCase
{
    /**
     * Test how PipelineOutput allocates new Table classes.
     *
     * @return void
     */
    public static function test_pipeline_output_generation_of_new_table_objects(): void
    {
        $output = new PipelineOutput();

        // reusableLater = (default)
        $table = $output->newTable();
        $table->row('A title 1', 'content 11');

        $table = $output->newTable();
        $table->row('A title 2222', 'content 2');

        $table = $output->reuseTableOrNew();
        $table->row('A title 3', 'content 3');

        $table = $output->reuseTableOrNew();
        $table->row('A title 4444', 'content 4');

        $table = $output->newTable();
        $table->row('A title 5', 'content 5');

        $table = $output->reuseTableOrNew();
        $table->row('A title 6666', 'content 6');



        // reusableLater = true
        $table = $output->newTable(true);
        $table->row('B title 1', 'content 11');

        $table = $output->newTable(true);
        $table->row('B title 2222', 'content 2');

        $table = $output->reuseTableOrNew(true);
        $table->row('B title 3', 'content 3');

        $table = $output->reuseTableOrNew(true);
        $table->row('B title 4444', 'content 4');

        $table = $output->newTable(true);
        $table->row('B title 5', 'content 5');

        $table = $output->reuseTableOrNew(true);
        $table->row('B title 6666', 'content 6');



        // reusableLater = false
        $table = $output->newTable(false);
        $table->row('C title 1', 'content 11');

        $table = $output->newTable(false);
        $table->row('C title 2222', 'content 2');

        $table = $output->reuseTableOrNew(false);
        $table->row('C title 3', 'content 3');

        $table = $output->reuseTableOrNew(false);
        $table->row('C title 4444', 'content 4');

        $table = $output->newTable(false);
        $table->row('C title 5', 'content 5');

        $table = $output->reuseTableOrNew(false);
        $table->row('C title 6666', 'content 6');



        $expectedOutput = 'A title 1  content 11' . PHP_EOL
                        . PHP_EOL
                        . 'A title 2222  content 2' . PHP_EOL // same table
                        . 'A title 3     content 3' . PHP_EOL // same table
                        . 'A title 4444  content 4' . PHP_EOL // same table
                        . PHP_EOL
                        . 'A title 5     content 5' . PHP_EOL // same table
                        . 'A title 6666  content 6' . PHP_EOL // same table
                        . PHP_EOL
                        . 'B title 1  content 11' . PHP_EOL
                        . PHP_EOL
                        . 'B title 2222  content 2' . PHP_EOL // same table
                        . 'B title 3     content 3' . PHP_EOL // same table
                        . 'B title 4444  content 4' . PHP_EOL // same table
                        . PHP_EOL
                        . 'B title 5     content 5' . PHP_EOL // same table
                        . 'B title 6666  content 6' . PHP_EOL // same table
                        . PHP_EOL
                        . 'C title 1  content 11' . PHP_EOL
                        . PHP_EOL
                        . 'C title 2222  content 2' . PHP_EOL
                        . PHP_EOL
                        . 'C title 3  content 3' . PHP_EOL
                        . PHP_EOL
                        . 'C title 4444  content 4' . PHP_EOL
                        . PHP_EOL
                        . 'C title 5  content 5' . PHP_EOL
                        . PHP_EOL
                        . 'C title 6666  content 6';
        self::assertSame($expectedOutput, $output->getCombinedOutput());
    }
    /**
     * Test how PipelineOutput allocates new Table classes.
     *
     * @return void
     */
    public static function test_pipeline_output_generation_of_new_text_objects(): void
    {
        $output = new PipelineOutput();

        // reusableLater = (default)
        $table = $output->newText();
        $table->line('A title 1');

        $table = $output->newText();
        $table->line('A title 2');

        $table = $output->reuseTextOrNew();
        $table->line('A title 3');

        $table = $output->reuseTextOrNew();
        $table->line('A title 4');

        $table = $output->newText();
        $table->line('A title 5');

        $table = $output->reuseTextOrNew();
        $table->line('A title 6');



        // reusableLater = true
        $table = $output->newText(true);
        $table->line('B title 1');

        $table = $output->newText(true);
        $table->line('B title 2');

        $table = $output->reuseTextOrNew(true);
        $table->line('B title 3');

        $table = $output->reuseTextOrNew(true);
        $table->line('B title 4');

        $table = $output->newText(true);
        $table->line('B title 5');

        $table = $output->reuseTextOrNew(true);
        $table->line('B title 6');



        // reusableLater = false
        $table = $output->newText(false);
        $table->line('C title 1');

        $table = $output->newText(false);
        $table->line('C title 2');

        $table = $output->reuseTextOrNew(false);
        $table->line('C title 3');

        $table = $output->reuseTextOrNew(false);
        $table->line('C title 4');

        $table = $output->newText(false);
        $table->line('C title 5');

        $table = $output->reuseTextOrNew(false);
        $table->line('C title 6');



        $expectedOutput = 'A title 1' . PHP_EOL
            . PHP_EOL
            . 'A title 2' . PHP_EOL // same text
            . 'A title 3' . PHP_EOL // same text
            . 'A title 4' . PHP_EOL // same text
            . PHP_EOL
            . 'A title 5' . PHP_EOL // same text
            . 'A title 6' . PHP_EOL // same text
            . PHP_EOL
            . 'B title 1' . PHP_EOL
            . PHP_EOL
            . 'B title 2' . PHP_EOL // same text
            . 'B title 3' . PHP_EOL // same text
            . 'B title 4' . PHP_EOL // same text
            . PHP_EOL
            . 'B title 5' . PHP_EOL // same text
            . 'B title 6' . PHP_EOL // same text
            . PHP_EOL
            . 'C title 1' . PHP_EOL
            . PHP_EOL
            . 'C title 2' . PHP_EOL
            . PHP_EOL
            . 'C title 3' . PHP_EOL
            . PHP_EOL
            . 'C title 4' . PHP_EOL
            . PHP_EOL
            . 'C title 5' . PHP_EOL
            . PHP_EOL
            . 'C title 6';
        self::assertSame($expectedOutput, $output->getCombinedOutput());
    }
}
