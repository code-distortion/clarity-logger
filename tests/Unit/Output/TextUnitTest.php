<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Output;

use CodeDistortion\ClarityLogger\Output\TableOutput;
use CodeDistortion\ClarityLogger\Output\TextOutput;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the Text class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class TextUnitTest extends PHPUnitTestCase
{
    /**
     * Test that the Text class accepts various inputs, and renders the output properly.
     *
     * @test
     *
     * @return void
     */
    public static function test_text_output_population_and_rendering(): void
    {
        // check instantiation values

        // no instantiation values
        $text = new TextOutput();
        self::assertSame('', $text->render());

        // instantiated with an empty array (no lines)
        $text = new TextOutput([]);
        self::assertSame('', $text->render());

        // instantiated with one line
        $text = new TextOutput(['hello']);
        self::assertSame('hello', $text->render());

        // instantiated with two lines
        $text = new TextOutput(['hello', 'there']);
        $expected = 'hello' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());

        // instantiated with an empty line in-between
        $text = new TextOutput(['hello', '', 'there']);
        $expected = 'hello' . PHP_EOL
                  . '' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());

        // instantiated with empty lines in-between
        $text = new TextOutput(['hello', '', '', 'there']);
        $expected = 'hello' . PHP_EOL
                  . '' . PHP_EOL
                  . '' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());



        // check addition of individual lines

        // check that new lines are added after the instantiation lines
        $text = (new TextOutput(['hello', 'there']))->line('again');
        $expected = 'hello' . PHP_EOL
            . 'there' . PHP_EOL
            . 'again';
        self::assertSame($expected, $text->render());

        // empty line
        $text = (new TextOutput())->line('');
        $expected = '';
        self::assertSame($expected, $text->render());

        // multiple empty lines
        $text = (new TextOutput())->line('')->line('');
        $expected = ''  . PHP_EOL
                  . '';
        self::assertSame($expected, $text->render());

        // empty line in the middle
        $text = (new TextOutput())->line('hello')->line('')->line('there');
        $expected = 'hello' . PHP_EOL
                  . '' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());

        // empty lines in-between
        $text = (new TextOutput())->line('hello')->line('')->line('')->line('there');
        $expected = 'hello' . PHP_EOL
                  . '' . PHP_EOL
                  . '' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());



        // test the addition of blank lines using blankLine()

        // with one blank line added
        $text = (new TextOutput())->line('hello')->line('there')->blankLine()->line('again');
        $expected = 'hello' . PHP_EOL
                  . 'there' . PHP_EOL
                  . '' . PHP_EOL
                  . 'again';
        self::assertSame($expected, $text->render());

        // with multiple blank lines added
        $text = (new TextOutput())->line('hello')->line('there')->blankLine()->blankLine()->line('again');
        $expected = 'hello' . PHP_EOL
                  . 'there' . PHP_EOL
                  . '' . PHP_EOL
                  . '' . PHP_EOL
                  . 'again';
        self::assertSame($expected, $text->render());



        // check addition of multiple lines

        // check that new lines are added after the instantiation lines
        $text = (new TextOutput(['hello', 'there']))->lines(['again']);
        $expected = 'hello' . PHP_EOL
                  . 'there' . PHP_EOL
                  . 'again';
        self::assertSame($expected, $text->render());

        // an empty line
        $text = (new TextOutput())->lines(['']);
        $expected = '';
        self::assertSame($expected, $text->render());

        // multiple empty lines
        $text = (new TextOutput())->lines(['', '']);
        $expected = ''  . PHP_EOL
                  . '';
        self::assertSame($expected, $text->render());

        // check that the lines() method accepts strings as well
        $text = (new TextOutput())->lines('hello')->lines('there');
        $expected = 'hello' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());

        // a single line added with lines()
        $text = (new TextOutput())->lines(['hello']);
        $expected = 'hello';
        self::assertSame($expected, $text->render());

        // multiple lines added with lines()
        $text = (new TextOutput())->lines(['hello', 'there']);
        $expected = 'hello' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());

        // multiple lines added with lines(), with an empty line in the middle
        $text = (new TextOutput())->lines(['hello', '', 'there']);
        $expected = 'hello' . PHP_EOL
                  . '' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());

        // multiple lines added with lines(), with empty lines in-between
        $text = (new TextOutput())->lines(['hello', '', '', 'there']);
        $expected = 'hello' . PHP_EOL
                  . '' . PHP_EOL
                  . '' . PHP_EOL
                  . 'there';
        self::assertSame($expected, $text->render());



        // check the combination of instantiation, line() and lines()
        $text = (new TextOutput(['hello']))->line('there')->lines(['again', 'and again'])->blankLine()->line('last');
        $expected = 'hello' . PHP_EOL
                  . 'there' . PHP_EOL
                  . 'again' . PHP_EOL
                  . 'and again' . PHP_EOL
                  . '' . PHP_EOL
                  . 'last';
        self::assertSame($expected, $text->render());
    }
}
