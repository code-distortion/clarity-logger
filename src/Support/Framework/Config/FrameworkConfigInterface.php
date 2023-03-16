<?php

namespace CodeDistortion\ClarityLogger\Support\Framework\Config;

/**
 * Interface for interacting with the current framework's configuration.
 */
interface FrameworkConfigInterface
{
    /**
     * Retrieve the project-root directory.
     *
     * @return string
     */
    public static function getProjectRoot(): string;

    /**
     * Retrieve the console command being run.
     *
     * @return string
     */
    public static function getConsoleCommand(): string;

    /**
     * Find out if a command is currently being run.
     *
     * @return boolean
     */
    public static function runningInConsole(): bool;

    /**
     * Retrieve the default channel.
     *
     * @return string
     */
    public static function getDefaultChannel(): string;

    /**
     * Retrieve the default level.
     *
     * @return string
     */
    public static function getDefaultExceptionLevel(): string;

    /**
     * Retrieve the default level for messages.
     *
     * @return string
     */
    public static function getDefaultMessageLevel(): string;

    /**
     * Retrieve the renderer classes per-channel.
     *
     * @return array<string, class-string>
     */
    public static function getRenderersPerChannel(): array;

    /**
     * Retrieve the default renderer class.
     *
     * @return class-string
     */
    public static function getDefaultRenderer(): string;

    /**
     * Retrieve the timezones to use.
     *
     * @return string[]
     */
    public static function getTimezones(): array;

    /**
     * Retrieve the format to render dates/times in.
     *
     * @return string[]
     */
    public static function getDateTimeFormat(): array;

    /**
     * Retrieve the prefix to use.
     *
     * @return string
     */
    public static function getPrefix(): string;

    /**
     * Retrieve the "use call stack order" setting.
     *
     * @return boolean
     */
    public static function getUseCallStackOrder(): bool;





//    /**
//     * Update the framework's config with new values (used while running tests).
//     *
//     * Note: For frameworks other than Laravel, the keys will need to be converted from Laravel's keys.
//     *
//     * @internal
//     *
//     * @param mixed[] $values The values to store.
//     * @return void
//     */
//    public static function updateConfig(array $values): void;
}
