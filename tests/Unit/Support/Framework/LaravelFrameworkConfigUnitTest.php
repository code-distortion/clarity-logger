<?php

namespace CodeDistortion\ClarityLogger\Tests\Unit\Support\Framework;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Helpers\CommandLineHelper;
use CodeDistortion\ClarityLogger\Renderers\Laravel\TextRenderer;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use CodeDistortion\ClarityLogger\Support\InternalSettings;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;

/**
 * Test the LaravelFrameworkConfig class.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class LaravelFrameworkConfigUnitTest extends LaravelTestCase
{
    /**
     * Test that the framework config object is cached, that it returns the same instance each time.
     *
     * @test
     *
     * @return void
     */
    public static function test_framework_config_caching(): void
    {
        self::assertSame(Framework::config(), Framework::config());
    }



    /**
     * Test that the project-root-directory is detected properly.
     *
     * @test
     *
     * @return void
     * @throws ClarityLoggerInitialisationException Doesn't throw this, but phpcs expects this to be here.
     */
    public static function test_project_root_dir_detection(): void
    {
        self::assertSame(
            realpath(__DIR__ . '/../../../../') . DIRECTORY_SEPARATOR,
            Framework::config()->getProjectRootDir()
        );
    }



    /**
     * Test the framework config crud functionality.
     *
     * @test
     *
     * @return void
     */
    public static function test_framework_config_crud(): void
    {
        $config = Framework::config();
        $key = 'a.key';



        // retrieving a BOOLEAN

        // when the value is a string - returns null
        $config->updateConfig([$key => 'abc']);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is a string with commas - returns null
        $config->updateConfig([$key => 'abc,def']);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is an empty string - returns null
        $config->updateConfig([$key => '']);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is null - returns null
        $config->updateConfig([$key => null]);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is an array - returns null
        $config->updateConfig([$key => ['abc' => 'def']]);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is an empty array - returns null
        $config->updateConfig([$key => []]);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is true - returns true
        $config->updateConfig([$key => true]);
        self::assertTrue($config->pickConfigBoolean($key));

        // when the value is false - returns false
        $config->updateConfig([$key => false]);
        self::assertFalse($config->pickConfigBoolean($key));

        // when the value is an integer - returns null
        $config->updateConfig([$key => 123]);
        self::assertNull($config->pickConfigBoolean($key));

        // when the value is a float - returns null
        $config->updateConfig([$key => 123.456]);
        self::assertNull($config->pickConfigBoolean($key));



        // retrieving a STRING

        // when the value is a string - returns the string
        $config->updateConfig([$key => 'abc']);
        self::assertSame('abc', $config->pickConfigString($key));

        // when the value is a string with commas - returns the string
        $config->updateConfig([$key => 'abc,def']);
        self::assertSame('abc,def', $config->pickConfigString($key));

        // when the value is an empty string - returns null
        $config->updateConfig([$key => '']);
        self::assertNull($config->pickConfigString($key));

        // when the value is null - returns null
        $config->updateConfig([$key => null]);
        self::assertNull($config->pickConfigString($key));

        // when the value is an array - returns null
        $config->updateConfig([$key => ['abc' => 'def']]);
        self::assertNull($config->pickConfigString($key));

        // when the value is an empty array - returns null
        $config->updateConfig([$key => []]);
        self::assertNull($config->pickConfigString($key));

        // when the value is true - returns null
        $config->updateConfig([$key => true]);
        self::assertNull($config->pickConfigString($key));

        // when the value is false - returns null
        $config->updateConfig([$key => false]);
        self::assertNull($config->pickConfigString($key));

        // when the value is an integer - returns null
        $config->updateConfig([$key => 123]);
        self::assertNull($config->pickConfigString($key));

        // when the value is a float - returns null
        $config->updateConfig([$key => 123.456]);
        self::assertNull($config->pickConfigString($key));



        // retrieving an ARRAY

        // when the value is a string - returns an array with the string as the value
        $config->updateConfig([$key => 'abc']);
        self::assertSame(['abc'], $config->pickConfigStringArray($key));

        // when the value is a string with commas - returns an array with the string as the value
        $config->updateConfig([$key => 'abc,def']);
        self::assertSame(['abc,def'], $config->pickConfigStringArray($key));

        // when the value is an empty string - returns an empty array
        $config->updateConfig([$key => '']);
        self::assertSame([], $config->pickConfigStringArray($key));

        // when the value is null - returns an empty array
        $config->updateConfig([$key => null]);
        self::assertSame([], $config->pickConfigStringArray($key));

        // when the value is an array - returns the array
        $config->updateConfig([$key => ['abc' => 'def']]);
        self::assertSame(['abc' => 'def'], $config->pickConfigStringArray($key));

        // when the value is an empty array - returns an empty array
        $config->updateConfig([$key => []]);
        self::assertSame([], $config->pickConfigStringArray($key));

        // when the value is true - returns an empty array
        $config->updateConfig([$key => true]);
        self::assertSame([], $config->pickConfigStringArray($key));

        // when the value is false - returns an empty array
        $config->updateConfig([$key => false]);
        self::assertSame([], $config->pickConfigStringArray($key));

        // when the value is an integer - returns an empty array
        $config->updateConfig([$key => 123]);
        self::assertSame([], $config->pickConfigStringArray($key));

        // when the value is a float - returns an empty array
        $config->updateConfig([$key => 123.456]);
        self::assertSame([], $config->pickConfigStringArray($key));
    }

    /**
     * Test the particular values that the framework config fetches.
     *
     * @test
     *
     * @return void
     */
    public static function test_framework_config_settings(): void
    {
        $config = Framework::config();
        $config->updateConfig(['logging.default' => 'default-channel']);



        // getConsoleCommand()
        self::assertSame(CommandLineHelper::renderCommandLine($_SERVER['argv'] ?? []), $config->getConsoleCommand());



        // runningInConsole()
        self::assertTrue($config->runningInConsole());



        // getFrameworkDefaultChannels()
        $config->updateConfig(['logging.default' => null]);
        self::assertSame(['stack'], $config->getFrameworkDefaultChannels());
        $config->updateConfig(['logging.default' => 'abc']);
        self::assertSame(['abc'], $config->getFrameworkDefaultChannels());



        // getDefaultExceptionLevel()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.exception' => null]);
        self::assertSame(Settings::REPORTING_LEVEL_ERROR, $config->getDefaultExceptionLevel()); // defaults to 'error'
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.exception' => '']);
        self::assertSame(Settings::REPORTING_LEVEL_ERROR, $config->getDefaultExceptionLevel()); // defaults to 'error'
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.exception' => 'abc']);
        self::assertSame('abc', $config->getDefaultExceptionLevel());



        // getDefaultMessageLevel()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.message' => null]);
        self::assertSame(Settings::REPORTING_LEVEL_INFO, $config->getDefaultMessageLevel()); // defaults to 'info'
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.message' => '']);
        self::assertSame(Settings::REPORTING_LEVEL_INFO, $config->getDefaultMessageLevel()); // defaults to 'info'
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.message' => 'abc']);
        self::assertSame('abc', $config->getDefaultMessageLevel());



        // getRenderersPerChannel()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.channels' => null]);
        self::assertSame([], $config->getRenderersPerChannel());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.channels' => '']);
        self::assertSame([], $config->getRenderersPerChannel());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.channels' => 'abc']);
        self::assertSame(['abc'], $config->getRenderersPerChannel());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.channels' => ['abc']]);
        self::assertSame(['abc'], $config->getRenderersPerChannel());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.channels' => ['a' => 'b']]);
        self::assertSame(['a' => 'b'], $config->getRenderersPerChannel());



        // getDefaultRenderer()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.default' => null]);
        self::assertSame(TextRenderer::class, $config->getDefaultRenderer()); // defaults to TextRenderer::class
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.default' => '']);
        self::assertSame(TextRenderer::class, $config->getDefaultRenderer()); // defaults to TextRenderer::class
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.default' => 'abc']);
        self::assertSame('abc', $config->getDefaultRenderer());



        // getTimezones()
        $config->updateConfig(['app.timezone' => null]);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => null]);
        self::assertSame(['UTC'], $config->getTimezones()); // defaults to ['UTC']

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => null]);
        self::assertSame(['APPTZ'], $config->getTimezones());

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => '']);
        self::assertSame(['APPTZ'], $config->getTimezones());

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => []]);
        self::assertSame(['APPTZ'], $config->getTimezones());

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => 'LOGGERTZ']);
        self::assertSame(['LOGGERTZ'], $config->getTimezones());

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => 'LTZ1,LTZ2']);
        self::assertSame(['LTZ1', 'LTZ2'], $config->getTimezones());

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => ['LOGGERTZ']]);
        self::assertSame(['LOGGERTZ'], $config->getTimezones());

        $config->updateConfig(['app.timezone' => 'APPTZ']);
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones' => ['LTZ1', 'LTZ2']]);
        self::assertSame(['LTZ1', 'LTZ2'], $config->getTimezones());



        // getDateTimeFormat()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.format' => null]);
        self::assertSame([], $config->getDateTimeFormat());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.format' => '']);
        self::assertSame([], $config->getDateTimeFormat());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.format' => 'r']);
        self::assertSame(['r'], $config->getDateTimeFormat());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.format' => ['']]);
        self::assertSame([''], $config->getDateTimeFormat());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.format' => ['r', 't']]);
        self::assertSame(['r', 't'], $config->getDateTimeFormat());



        // getPrefix()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.prefix' => null]);
        self::assertSame('', $config->getPrefix());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.prefix' => '']);
        self::assertSame('', $config->getPrefix());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.prefix' => 'abc']);
        self::assertSame('abc', $config->getPrefix());



        // getUseCallStackOrder()
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.oldest_first' => null]);
        self::assertSame(true, $config->getUseCallStackOrder());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.oldest_first' => false]);
        self::assertSame(false, $config->getUseCallStackOrder());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.oldest_first' => true]);
        self::assertSame(true, $config->getUseCallStackOrder());
        $config->updateConfig([InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.oldest_first' => 'abc']);
        self::assertSame(true, $config->getUseCallStackOrder());
    }
}
