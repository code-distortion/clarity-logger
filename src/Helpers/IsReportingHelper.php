<?php

namespace CodeDistortion\ClarityLogger\Helpers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Pipeline\Pipeline;
use Illuminate\Foundation\Console\Kernel;

/**
 * Check if an exception is being "reported" (e.g. report($e)), or if it's bubbled up to the top where the framework is
 * dealing with it.
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

        $lastFrameWasExceptionHandlerReport = false;
        foreach ($stacktrace as $frame) {

            $class = $frame['class'] ?? null;
            $object = $frame['object'] ?? null;
            $method = $frame['function'];



            // check for a reporting call (e.g. report($e)), which confirms the answer straight away
            if (self::isAReportingCall($class, $method)) {
                return true;
            }



            // for WEB REQUESTS

            // this pattern means that the exception bubbled up to the top and Laravel is dealing with it

            // look for the pattern where:
            // Illuminate\Contracts\Pipeline\Pipeline@handleException calls
            // Illuminate\Contracts\Debug\ExceptionHandler@report

            if ($lastFrameWasExceptionHandlerReport) {
                if (self::isLaravelPipelineHandleException($object, $method)) {
                    return false;
                }
            }



            // for COMMANDS

            // this pattern means that the exception bubbled up to the top and Laravel is dealing with it

            // look for the pattern where:
            // Illuminate\Contracts\Console\Kernel@reportException calls
            // Illuminate\Contracts\Debug\ExceptionHandler@report

            if ($lastFrameWasExceptionHandlerReport) {
                if (self::isLaravelConsoleKernel($object, $method)) {
                    return false;
                }
            }



            $lastFrameWasExceptionHandlerReport = self::isLaravelExceptionHandlerReport($object, $method);
        }

        return true;
    }



    /**
     * Check to see if a reporting method is being called (e.g. report($e)).
     *
     * @param string|null $class  The class being called from.
     * @param string|null $method The method being called.
     * @return boolean
     */
    private static function isAReportingCall(?string $class, ?string $method): bool
    {
        return in_array(
            "$class@$method",
            [
                '@report',
                '@report_if',
                '@report_unless',
            ]
        );
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

    /**
     * Check to see if the object and method is "Illuminate\Contracts\Console\Kernel@reportException".
     *
     * @param object|null $object The object being called from.
     * @param string|null $method The method being called.
     * @return boolean
     */
    private static function isLaravelConsoleKernel(?object $object, ?string $method): bool
    {
        // e.g. 'Illuminate\Foundation\Console\Kernel@reportException'
        if (!$object instanceof Kernel) {
            return false;
        }

        if ($method != 'reportException') {
            return false;
        }

        return true;
    }
}
