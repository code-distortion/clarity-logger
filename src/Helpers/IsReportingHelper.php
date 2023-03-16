<?php

namespace CodeDistortion\ClarityLogger\Helpers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Pipeline\Pipeline;

/**
 * Methods to check if an exception is being "reported" (as opposed to being thrown, and not caught by application
 * code).
 */
class IsReportingHelper
{
    /**
     * Check to see if an exception is being "reported".
     *
     * Looks for the Laravel "report(..)" helper in the current call-stack.
     *
     * @return boolean
     */
    public static function checkIfReporting(): bool
    {
        $stacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);

        $allowedCalls = [
            '@report',
            '@report_if',
            '@report_unless',
        ];

        $lastFrameWasExceptionHandlerReport = false;
        foreach ($stacktrace as $frame) {

            // check for an allowed call, which gives an automatic pass
            $class = $frame['class'] ?? null;
            $method = $frame['function'];
            if (in_array("$class@$method", $allowedCalls)) {
                return true;
            }

            // look for the pattern where
            // Illuminate\Contracts\Pipeline\Pipeline@handleException calls
            // Illuminate\Contracts\Debug\ExceptionHandler@report
            // which means that the exception was not caught
            $object = $frame['object'] ?? null;
            if ($lastFrameWasExceptionHandlerReport) {
                if (self::isLaravelPipelineHandleException($object, $method)) {
                    return false;
                }
            }

            $lastFrameWasExceptionHandlerReport = self::isLaravelExceptionHandlerReport($object, $method);
        }

        return true;
    }

    /**
     * Check to see if the object and method is "Illuminate\Contracts\Debug\ExceptionHandler@report".
     *
     * @param object|null $object The object being called from.
     * @param string|null $method The method being called.
     * @return boolean
     */
    private static function isLaravelExceptionHandlerReport(?object $object, ?string $method): bool
    {
        // e.g. 'Illuminate\Foundation\Exceptions\Handler@report'
        if (!$object instanceof ExceptionHandler) {
            return false;
        }

        if ($method != 'report') {
            return false;
        }

        return true;
    }

    /**
     * Check to see if the object and method is "Illuminate\Contracts\Pipeline\Pipeline@handleException".
     *
     * @param object|null $object The object being called from.
     * @param string|null $method The method being called.
     * @return boolean
     */
    private static function isLaravelPipelineHandleException(?object $object, ?string $method): bool
    {
        // e.g. 'Illuminate\Routing\Pipeline@handleException'
        if (!$object instanceof Pipeline) {
            return false;
        }

        if ($method != 'handleException') {
            return false;
        }

        return true;
    }
}
