<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Helpers;

use CodeDistortion\ClarityLogger\Helpers\FileHelper;
use CodeDistortion\ClarityLogger\Tests\PHPUnitTestCase;

/**
 * Test the FileHelper class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class FileHelperUnitTest extends PHPUnitTestCase
{
    /**
     * Test the method that removes a base directory from a path.
     *
     * @test
     * @dataProvider removeBaseDirDataProvider
     *
     * @param string      $baseDir  The base directory to remove.
     * @param string|null $path     The path to remove it from.
     * @param string|null $expected The expected output.
     * @return void
     */
    public static function test_remove_base_dir_method(string $baseDir, ?string $path, ?string $expected): void
    {
        $baseDir = str_replace('/', DIRECTORY_SEPARATOR, $baseDir);

        if (is_string($path)) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }

        if (is_string($expected)) {
            $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        }

        self::assertSame($expected, FileHelper::removeBaseDir($baseDir, $path));
    }

    /**
     * DataProvider for test_remove_base_dir_method().
     *
     * @return array<array<string, string|null>>
     */
    public static function removeBaseDirDataProvider(): array
    {
        $return = [];



        $return[] = [
            'baseDir' => '',
            'path' => null,
            'expected' => null,
        ];

        $return[] = [
            'baseDir' => '',
            'path' => '',
            'expected' => null,
        ];

        $return[] = [
            'baseDir' => '',
            'path' => '/path/to/base/file.php',
            'expected' => '/path/to/base/file.php',
        ];

        $return[] = [
            'baseDir' => '',
            'path' => '/other/path/file.php',
            'expected' => '/other/path/file.php',
        ];



        $return[] = [
            'baseDir' => '/path/to/base',
            'path' => null,
            'expected' => null,
        ];

        $return[] = [
            'baseDir' => '/path/to/base',
            'path' => '',
            'expected' => null,
        ];

        $return[] = [
            'baseDir' => '/path/to/base',
            'path' => '/path/to/base/file.php',
            'expected' => '/file.php',
        ];

        $return[] = [
            'baseDir' => '/path/to/base',
            'path' => '/other/path/file.php',
            'expected' => '/other/path/file.php',
        ];



        $return[] = [
            'baseDir' => '/path/to/base/', // <<< with trailing /
            'path' => null,
            'expected' => null,
        ];

        $return[] = [
            'baseDir' => '/path/to/base/', // <<< with trailing /
            'path' => '',
            'expected' => null,
        ];

        $return[] = [
            'baseDir' => '/path/to/base/', // <<< with trailing /
            'path' => '/path/to/base/file.php',
            'expected' => '/file.php',
        ];

        $return[] = [
            'baseDir' => '/path/to/base/', // <<< with trailing /
            'path' => '/other/path/file.php',
            'expected' => '/other/path/file.php',
        ];

        return $return;
    }



    /**
     * Test the method that renders a location.
     *
     * @test
     * @dataProvider renderLocationDataProvider
     *
     * @param string      $file               The file being used.
     * @param integer     $line               The line being used.
     * @param string|null $currentMethod      The current method being used.
     * @param boolean     $showAsLastAppFrame Whether to show the location as the last app frame.
     * @param string      $expected           The expected output.
     * @return void
     */
    public static function test_render_location_method(
        string $file,
        int $line,
        ?string $currentMethod,
        bool $showAsLastAppFrame,
        string $expected,
    ): void {

        $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);

        self::assertSame($expected, FileHelper::renderLocation($file, $line, $currentMethod, $showAsLastAppFrame));
    }

    /**
     * DataProvider for test_render_location_method().
     *
     * @return array<array<string, string|integer|boolean|null>>
     */
    public static function renderLocationDataProvider(): array
    {
        $return = [];

        $return[] = [
            'file' => '/path/to/file.php',
            'line' => 100,
            'currentMethod' => null,
            'showAsLastAppFrame' => false,
            'expected' => 'path/to/file.php on line 100',
        ];

        $return[] = [
            'file' => '/path/to/file.php',
            'line' => 100,
            'currentMethod' => 'abc',
            'showAsLastAppFrame' => false,
            'expected' => 'path/to/file.php on line 100 (abc)',
        ];

        $return[] = [
            'file' => '/path/to/file.php',
            'line' => 100,
            'currentMethod' => null,
            'showAsLastAppFrame' => true,
            'expected' => 'path/to/file.php on line 100 (last application frame)',
        ];

        $return[] = [
            'file' => '/path/to/file.php',
            'line' => 100,
            'currentMethod' => 'abc',
            'showAsLastAppFrame' => true,
            'expected' => 'path/to/file.php on line 100 (abc) (last application frame)',
        ];

        return $return;
    }
}
