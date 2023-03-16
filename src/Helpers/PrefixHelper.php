<?php

namespace CodeDistortion\ClarityLogger\Helpers;

/**
 * Methods to add prefixes to each line.
 */
class PrefixHelper
{
    /**
     * Add a prefix string to each line.
     *
     * @param string $prefix The prefix to add.
     * @param string $lines  The lines to update.
     * @return string
     */
    public static function add(string $prefix, string $lines): string
    {
        if (!mb_strlen($prefix)) {
            return $lines;
        }

        $lines = explode(PHP_EOL, $lines);

        foreach ($lines as $index => $line) {
//            $lines[$index] = "$prefix$line";
            $lines[$index] = mb_strlen($line)
                ? "$prefix$line"
                : rtrim($prefix);
        }

        return implode(PHP_EOL, $lines);
    }
}
