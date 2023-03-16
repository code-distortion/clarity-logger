<?php

namespace CodeDistortion\ClarityLogger\Helpers;

/**
 * Methods to help with method related things.
 */
class MethodHelper
{
    /**
     * Resolve the name of the current method/function/closure.
     *
     * @param string|null $class    The frame's class (e.g. from a stack trace frame).
     * @param string|null $function The frame's function (e.g. from a stack trace frame).
     * @return string
     */
    public static function resolveCurrentMethod(?string $class, ?string $function): string
    {
        if (!$function) {
            return '';
        }

        if (str_ends_with($function, '{closure}')) {
            return "closure";
        }

        return ($class ?? null)
            ? "method \"{$function}\""
            : "function \"{$function}\"";
    }
}
