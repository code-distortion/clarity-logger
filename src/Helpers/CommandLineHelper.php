<?php

namespace CodeDistortion\ClarityLogger\Helpers;

/**
 * Methods to help with command-line related things.
 */
class CommandLineHelper
{
    /**
     * Escape the given command-line arguments, and render them together as a string.
     *
     * @param string[] $args The arguments to escape (e.g. from $_SERVER['argv']).
     * @return string
     */
    public static function renderCommandLine(array $args): string
    {
        $processedArgs = [];
        foreach ($args as $arg) {
            $processedArgs[] = self::argNeedsEscaping($arg)
                ? escapeshellarg($arg)
                : $arg;
        }

        return implode(' ', $processedArgs);
    }

    /**
     * Check to see if a command-line argument needs escaping.
     *
     * @param string $arg The argument to check.
     * @return boolean
     */
    private static function argNeedsEscaping(string $arg): bool
    {
        if (!mb_strlen($arg)) {
            return true;
        }

        if (str_contains($arg, ' ')) {
            return true;
        }

        // check to see if the escaped version is different to the original
        $escapedArg = escapeshellarg($arg);
        $escapedArg = mb_substr($escapedArg, 1, -1);
        return $escapedArg != $arg;
    }
}
