<?php

namespace CodeDistortion\ClarityLogger\Support\Framework\Config;

use CodeDistortion\ClarityLogger\Helpers\CommandLineHelper;
use CodeDistortion\ClarityLogger\Renderers\Laravel\TextRenderer;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\InternalSettings;
use Illuminate\Contracts\Foundation\Application;

/**
 * Interacting with the Laravel's configuration.
 */
class LaravelFrameworkConfig implements FrameworkConfigInterface
{
    /**
     * Retrieve the project-root directory.
     *
     * @return string
     */
    public static function getProjectRootDir(): string
    {
        $path = self::isUsingTestbench()
            ? realpath(base_path('../../../../'))
            : realpath(base_path());

        return is_string($path)
            ? $path . DIRECTORY_SEPARATOR
            : '';
    }

    /**
     * Generate the console command being run.
     *
     * @return string
     */
    public static function getConsoleCommand(): string
    {
        if (!self::runningInConsole()) {
            return '';
        }

        return CommandLineHelper::renderCommandLine($_SERVER['argv'] ?? []);
    }

    /**
     * Find out if a command is currently being run.
     *
     * @return boolean
     */
    public static function runningInConsole(): bool
    {
        /** @var Application $app */
        $app = app();

        return $app->runningInConsole();
    }

    /**
     * Retrieve the framework's default channels.
     *
     * @return string[]
     */
    public static function getFrameworkDefaultChannels(): array
    {
        return array_filter(
            [self::pickConfigString('logging.default') ?: 'stack']
        );
    }



    /**
     * Retrieve the default level for exceptions.
     *
     * @return string
     */
    public static function getDefaultExceptionLevel(): string
    {
        return self::pickConfigString(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.exception')
            ?: Settings::REPORTING_LEVEL_ERROR;
    }

    /**
     * Retrieve the default level for messages.
     *
     * @return string
     */
    public static function getDefaultMessageLevel(): string
    {
        return self::pickConfigString(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.levels.message')
            ?: Settings::REPORTING_LEVEL_INFO;
    }

    /**
     * Retrieve the renderers per-channel.
     *
     * @return array<string, class-string>
     */
    public static function getRenderersPerChannel(): array
    {
        /** @var array<string, class-string> $renderersPerChannel */
        $renderersPerChannel = self::pickConfigStringArray(
            InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.channels'
        );
        return $renderersPerChannel;
    }

    /**
     * Retrieve the default renderer class.
     *
     * @return class-string
     */
    public static function getDefaultRenderer(): string
    {
        /** @var class-string|null $defaultRenderer */
        $defaultRenderer = self::pickConfigString(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.renderers.default');
        return $defaultRenderer ?: TextRenderer::class;
    }

    /**
     * Retrieve the timezones to use.
     *
     * @return string[]
     */
    public static function getTimezones(): array
    {
        // see if it's a string first, so it can be split by commas below
        $timezones = self::pickConfigString(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones')
            ?? self::pickConfigStringArray(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.timezones')
            ?: self::pickConfigString('app.timezone')
            ?: 'UTC';

        return is_array($timezones)
            ? $timezones
            : explode(',', $timezones);
    }

    /**
     * Retrieve the format to render dates/times in.
     *
     * @return string[]
     */
    public static function getDateTimeFormat(): array
    {
        return self::pickConfigStringArray(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.time.format');
    }

    /**
     * Retrieve the prefix to use.
     *
     * @return string
     */
    public static function getPrefix(): string
    {
        return (string) self::pickConfigString(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.prefix');
    }

    /**
     * Retrieve the "use call stack order" setting.
     *
     * @return boolean
     */
    public static function getUseCallStackOrder(): bool
    {
        return self::pickConfigBoolean(InternalSettings::LARAVEL_LOGGER__CONFIG_NAME . '.oldest_first') !== false;
    }





    /**
     * Update the framework's config with new values (used while running tests).
     *
     * Note: For frameworks other than Laravel, the keys will need to be converted from Laravel's keys.
     *
     * @internal
     *
     * @param mixed[] $values The values to store.
     * @return void
     */
    public static function updateConfig(array $values): void
    {
        config($values);
    }





    /**
     * Pick a boolean from Laravel's config.
     *
     * @internal
     *
     * @param string $key The key to look for.
     * @return boolean|null
     */
    public static function pickConfigBoolean(string $key): ?bool
    {
        $value = config($key);

        return is_bool($value)
            ? $value
            : null;
    }

    /**
     * Pick a string from Laravel's config.
     *
     * @internal
     *
     * @param string $key The key to look for.
     * @return string|null
     */
    public static function pickConfigString(string $key): ?string
    {
        $value = config($key);

        return (is_string($value)) && ($value !== '')
            ? $value
            : null;
    }

    /**
     * Pick a string or array of strings from Laravel's config. Returns them as an array.
     *
     * @internal
     *
     * @param string $key The key to look for.
     * @return string[]
     */
    public static function pickConfigStringArray(string $key): array
    {
        $values = config($key);

        if (is_string($values)) {
            return $values !== ''
                ? [$values]
                : [];
        }

        return is_array($values)
            ? $values
            : [];
    }

    /**
     * Work out if Orchestra Testbench is being used.
     *
     * @return boolean
     */
    private static function isUsingTestbench(): bool
    {
        $testBenchDir = '/vendor/orchestra/testbench-core/laravel';
        // @infection-ignore-all - UnwrapStrReplace - always gives the same result on linux
        $testBenchDir = str_replace('/', DIRECTORY_SEPARATOR, $testBenchDir);
        return str_ends_with(base_path(), $testBenchDir);
    }
}
