<?php

namespace CodeDistortion\ClarityLogger\Helpers;

use CodeDistortion\ClarityLogger\Support\Support;

/**
 * Methods to resolve the location that made a call to this package.
 */
class CallerLocationHelper
{
    /**
     * Resolve the location that made a call to this package, and return as a readable string.
     *
     * @param string        $baseDir          The project's base-dir, to make the path more readable.
     * @param string[]|null $ignoreNamespaces The package namespaces to ignore when looking for the frame.
     * @return string|null
     */
    public static function renderLocation(string $baseDir, ?array $ignoreNamespaces = null): ?string
    {
        $frame = self::findCalledLocation($ignoreNamespaces);
        if (!$frame) {
            return null;
        }

        return FileHelper::renderLocation(
            self::getFrameFileReadable($frame, $baseDir),
            self::getFrameLine($frame),
            MethodHelper::resolveCurrentMethod(self::getFrameClass($frame), self::getFrameFunction($frame)),
            false
        );
    }

    /**
     * Find the debug-backtrace point, from before this reporting library was called.
     *
     * @param string[]|null $ignoreNamespaces The package namespaces to ignore when looking for the frame.
     * @return mixed[]|null
     */
    private static function findCalledLocation(?array $ignoreNamespaces = []): ?array
    {
        $ignoreNamespaces = $ignoreNamespaces
            ?? [
                'CodeDistortion\\ClarityLogger\\',
                'Illuminate\\Container\\',
            ];

        $stacktrace = Support::preparePHPStackTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        foreach ($stacktrace as $frame) {

            $class = self::getFrameClass($frame);
            if (!mb_strlen($class)) {
                continue;
            }

            // special case, clarity-logger TEST frames *should* be detected
            if (str_starts_with($class, 'CodeDistortion\\ClarityLogger\\Tests\\')) {
                return $frame;
            }

            foreach ($ignoreNamespaces as $namespace) {
                if (str_starts_with($class, $namespace)) {
                    continue 2;
                }
            }

            return $frame;
        }

        return null;
    }

    /**
     * Pick the file from a debug_backtrace frame, and make it readable.
     *
     * @param mixed[] $frame   A frame from debug_backtrace.
     * @param string  $baseDir The project's base-dir, to make the path more readable.
     * @return string
     */
    private static function getFrameFileReadable(array $frame, string $baseDir): string
    {
        $path = is_string($frame['file']) ? $frame['file'] : '';

        return FileHelper::removeBaseDir($baseDir, $path) ?? '';
    }

    /**
     * Pick the file from a debug_backtrace frame.
     *
     * @param mixed[] $frame A frame from debug_backtrace.
     * @return integer
     */
    private static function getFrameLine(array $frame): int
    {
        $line = $frame['line'] ?? 0;
        return is_int($line) ? $line : 0;
    }

    /**
     * Pick the class from a debug_backtrace frame.
     *
     * @param mixed[] $frame A frame from debug_backtrace.
     * @return string
     */
    private static function getFrameClass(array $frame): string
    {
        $class = $frame['class'] ?? '';
        return is_string($class) ? $class : '';
    }

    /**
     * Pick the function from a debug_backtrace frame.
     *
     * @param mixed[] $frame A frame from debug_backtrace.
     * @return string
     */
    private static function getFrameFunction(array $frame): string
    {
        $function = $frame['function'] ?? '';
        return is_string($function) ? $function : '';
    }
}
