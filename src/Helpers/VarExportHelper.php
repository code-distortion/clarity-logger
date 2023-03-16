<?php

namespace CodeDistortion\ClarityLogger\Helpers;

/**
 * Methods to generate readable representations of variables.
 */
class VarExportHelper
{
    /**
     * Outputs or returns a parsable string representation of a value.
     *
     * @link https://php.net/manual/en/function.var-export.php
     * @param mixed  $expression The variable to export.
     * @param string $prefix     The prefix to add to each line.
     * @param string $indent     The string to use as indentation for child levels.
     * @return string
     */
    public static function export(mixed $expression, string $prefix = '', string $indent = '  '): string
    {
        $varExport = function ($expression, int $depth = 0) use (&$varExport, $prefix, $indent) {
            // process an array
            if (is_array($expression)) {
                if ($expression === []) {
                    return '[]';
                }
                $return = '[' . PHP_EOL;
                foreach ($expression as $index => $value) {
                    $return .=
                        $prefix
                        . str_repeat($indent, $depth + 1)
                        . (is_int($index) ? "$index => " : "'$index' => ")
                        . $varExport($value, $depth + 1) . ',' . PHP_EOL;
                }
                return $return . $prefix . str_repeat($indent, $depth) . ']';
                // process everything else
            } else {
                return var_export($expression, true);
            }
        };

        return $varExport($expression);
    }

    /**
     * Outputs or returns a parsable string representation of a value, in a nice format for reading.
     *
     * @link https://php.net/manual/en/function.var-export.php
     * @param mixed  $expression        The variable to export.
     * @param string $topLevelPrefix    The prefix to add to each line, when it's not a value at the top level.
     * @param string $secondLevelPrefix The prefix to add to each line, when it's not a value at the top level.
     * @param string $indent            The string to use as indentation for child levels.
     * @return string
     */
    public static function niceExport(
        mixed $expression,
        string $topLevelPrefix = '',
        string $secondLevelPrefix = '',
        string $indent = '  '
    ): string {

        if (is_array($expression)) {
            $return = '';
            $count = 0;
            foreach ($expression as $index => $value) {
                $return .= "$topLevelPrefix$index = " . self::export($value, $secondLevelPrefix, $indent);
                if (++$count < count($expression)) {
                    $return .= PHP_EOL;
                }
            }
            return $return;
        } else {
            return self::export($expression, $topLevelPrefix, $indent);
        }
    }
}
