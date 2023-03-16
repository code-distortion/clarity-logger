<?php

namespace CodeDistortion\ClarityLogger\Support;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Settings;

/**
 * Common methods, shared by this package.
 */
class Support
{
    /**
     * Check to make sure the given level is allowed.
     *
     * @internal
     *
     * @param string $level The level to check.
     * @return void
     * @throws ClarityLoggerInitialisationException Thrown when an invalid level is specfied.
     */
    public static function ensureLevelIsValid(string $level): void
    {
        if (in_array($level, Settings::LOG_LEVELS)) {
            return;
        }

        throw ClarityLoggerInitialisationException::levelNotAllowed($level);
    }



    /**
     * Resolve the current PHP callstack. Then tweak it, so it's in a format that's good for comparing.
     *
     * @internal
     *
     * @param array<integer, mixed[]> $phpStackTrace The stack trace to alter.
     * @param string|null             $file          The first file to shift onto the beginning.
     * @param integer|null            $line          The first line to shift onto the beginning.
     * @return array<integer, mixed[]>
     */
    public static function preparePHPStackTrace(
        array $phpStackTrace,
        ?string $file = null,
        ?int $line = null
    ): array {

        // shift the file and line values by 1 frame
        $newStackTrace = [];
        foreach ($phpStackTrace as $frame) {

            $nextFile = $frame['file'] ?? '';
            $nextLine = $frame['line'] ?? 0;

            $frame['file'] = $file;
            $frame['line'] = $line;
            $newStackTrace[] = $frame;

            $file = $nextFile;
            $line = $nextLine;
        }

        $newStackTrace[] = [
            'file' => $file,
            'line' => $line,
            'function' => '[top]',
            'args' => [],
        ];

        // a very edge case…
        //
        // e.g. call_user_func_array([new Context(), 'add'], ['something']);
        //
        // when Context methods are called via call_user_func_array(..), the callstack's most recent frame is an extra
        // frame that's missing the "file" and "line" keys
        //
        // this causes clarity not to remember meta-data, because it's associated to a "phantom" frame that's forgotten
        // the moment the callstack is inspected next
        //
        // skipping this frame brings the most recent frame back to the place where call_user_func_array was called
        //
        // @infection-ignore-all - FunctionCallRemoval - prevents timeout of array_shift(..) below
        while (
            (count($newStackTrace))
            && (($newStackTrace[0]['file'] == '') || ($newStackTrace[0]['line'] == 0))
        ) {
            array_shift($newStackTrace);
        }

        // turn objects into spl_object_ids
        // - so we're not unnecessarily holding on to references to these objects (in case that matters for the caller),
        // - and to reduce memory requirements
        foreach ($newStackTrace as $index => $step) {

            $object = $step['object']
                ?? null;

            if (is_object($object)) {
                $newStackTrace[$index]['object'] = spl_object_id($step['object']);
            }

            // remove the args, as they can cause unnecessary memory usage during runs of the test-suite
            // this happens when there are a lot of tests, as phpunit can pass large arrays of arg values
            unset($newStackTrace[$index]['args']);
        }

        return $newStackTrace;
    }

    /**
     * Remove Laravel's exception handler methods from the top of the stack trace.
     *
     * @internal
     *
     * @param array<integer, mixed[]> $stackTrace The stack trace to alter.
     * @return array<integer, mixed[]>
     */
    public static function pruneLaravelExceptionHandlerFrames(array $stackTrace): array
    {
        if (!count($stackTrace)) {
            return [];
        }

        $class = is_string($stackTrace[0]['class'] ?? null) ? $stackTrace[0]['class'] : '';

        while (str_starts_with($class, 'Illuminate\Foundation\Bootstrap\HandleExceptions')) {
            array_shift($stackTrace);

            $class = is_string($stackTrace[0]['class'] ?? null) ? $stackTrace[0]['class'] : '';
        }

        return $stackTrace;
    }
}
