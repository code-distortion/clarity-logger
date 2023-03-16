<?php

namespace CodeDistortion\ClarityLogger\Helpers;

/**
 * Methods to help with file related things.
 */
class FileHelper
{
    /**
     * Remove the base-path (e.g. "/var/www/html/") from a path.
     *
     * @param string      $baseDir The base directory to remove.
     * @param string|null $path    The path to alter.
     * @return string|null
     */
    public static function removeBaseDir(string $baseDir, ?string $path): ?string
    {
        if (is_null($path)) {
            return null;
        }
        if (!mb_strlen($path)) {
            return null;
        }
        if (!mb_strlen($baseDir)) {
            return $path;
        }

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($path, $baseDir)
            ? DIRECTORY_SEPARATOR . mb_substr($path, mb_strlen($baseDir))
            : $path;
    }

    /**
     * Render the location message.
     *
     * @param string      $file               The file the location is from.
     * @param integer     $line               The line the location is from.
     * @param string|null $currentMethod      The current method that was called.
     * @param boolean     $showAsLastAppFrame Whether the frame is the last application frame or not.
     * @return string
     */
    public static function renderLocation(
        string $file,
        int $line,
        ?string $currentMethod,
        bool $showAsLastAppFrame
    ): string {

        $parts = [];

        $file = ltrim($file, DIRECTORY_SEPARATOR);
        $parts[] = "$file on line $line";

        if ($currentMethod) {
            $parts[] = "($currentMethod)";
        }

        if ($showAsLastAppFrame) {
            $parts[] = "(last application frame)";
        }

        return implode(' ', $parts);
    }
}
