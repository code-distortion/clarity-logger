<?php

namespace CodeDistortion\ClarityLogger\Helpers;

use CodeDistortion\ClarityLogger\Output\TableOutput;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Support;
use Throwable;

/**
 * Resolve details about an exception.
 */
class ExceptionHelper
{
    /** @var string|null The application file the exception occurred in. */
    private ?string $appPath;

    /** @var integer|null The application file line number the exception occurred in. */
    private ?int $appLine;

    /** @var string|null The application's current method/function/closure name . */
    private ?string $appCurrentMethod = null;



    /** @var string|null The vendor file the exception occurred in. */
    private ?string $realPath = null;

    /** @var integer|null The vendor file line number the exception occurred in. */
    private ?int $realLine = null;

    /** @var string|null The application's current method/function/closure name . */
    private ?string $realCurrentMethod = null;





    /**
     * Constructor.
     *
     * @param Throwable $exception The exception to use.
     * @param string    $baseDir   The project's base directory.
     */
    public function __construct(
        private Throwable $exception,
        private string $baseDir,
    ) {
    }



    /**
     * Render the type and message in a readable way.
     *
     * @return string|null
     */
    public function renderTypeAndMessage(): ?string
    {
        $code = $this->exception->getCode()
            ? " (code {$this->exception->getCode()})"
            : '';

        return get_class($this->exception) . ": \"{$this->exception->getMessage()}\"$code";
    }



    /**
     * Find out if this exception has an application location (one that's different to the "real" location).
     *
     * @return boolean
     */
    public function hasAppLocation(): bool
    {
        $this->initialiseLocations();

        return !is_null($this->appPath);
    }

    /**
     * Render the exception's location, in a readable way.
     *
     * Will return the last app-frame's location if it's different to the real location.
     *
     * @return string|null
     */
    public function renderAppLocation(): ?string
    {
        $this->initialiseLocations();

        if (is_null($this->appPath)) {
            return null;
        }

        if (is_null($this->appLine)) {
            return null;
        }

        return FileHelper::renderLocation($this->appPath, $this->appLine, $this->appCurrentMethod, false);
    }

    /**
     * Render the exception's real location, in a readable way.
     *
     * Will return the last app-frame's location if it's different to the real location.
     *
     * @return string|null
     */
    public function renderRealLocation(): ?string
    {
        $this->initialiseLocations();

        if (is_null($this->realPath)) {
            return null;
        }

        if (is_null($this->realLine)) {
            return null;
        }

        return FileHelper::renderLocation($this->realPath, $this->realLine, $this->realCurrentMethod, false);
    }





    /**
     * Initialise the exception locations if they haven't been already.
     *
     * @return void
     */
    private function initialiseLocations(): void
    {
        if (!is_null($this->realPath)) {
            return;
        }

        $this->resolveExceptionLocation();
    }

    /**
     * Resolve the locations related to where the exception occurred.
     *
     * @return void
     */
    private function resolveExceptionLocation(): void
    {
        $baseDir = $this->baseDir;

        $this->appPath = $this->appLine = null;
        $isFirstFrame = true;

        $stackTrace = Support::preparePHPStackTrace(
            $this->exception->getTrace(),
            $this->exception->getFile(),
            $this->exception->getLine()
        );
        $stackTrace = Support::pruneLaravelExceptionHandlerFrames($stackTrace);

        foreach ($stackTrace as $frame) {

            $path = is_string($frame['file']) ? $frame['file'] : null;
            $projectPath = (string) FileHelper::removeBaseDir($baseDir, $path);
            $isVendorFrame = str_starts_with($projectPath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

            $class = is_string($frame['class'] ?? null) ? $frame['class'] : null;
            $function = is_string($frame['function'] ?? null) ? $frame['function'] : null;
            $line = is_int($frame['line']) ? $frame['line'] : null;

            if ($isFirstFrame) {
                $this->realPath = ltrim($projectPath, DIRECTORY_SEPARATOR);
                $this->realLine = $line;
                $this->realCurrentMethod = MethodHelper::resolveCurrentMethod($class, $function);
                $isFirstFrame = false;
            }

            if ($isVendorFrame) {
                continue;
            }

            $this->appPath = ltrim($projectPath, DIRECTORY_SEPARATOR);
            $this->appLine = $line;
            $this->appCurrentMethod = MethodHelper::resolveCurrentMethod($class, $function);

            break;
        }
    }



    /**
     * Render an exception's details into a TableOutput.
     *
     * @param TableOutput  $table   The table to add lines to.
     * @param Throwable    $e       The exception to render details about.
     * @param string       $baseDir The project's base directory.
     * @param integer|null $count   The number to show next to the exception.
     * @return void
     */
    public static function renderExceptionToTable(
        TableOutput $table,
        Throwable $e,
        string $baseDir,
        ?int $count = null,
    ): void {

        $exceptionCount = 0;
        do {

            $exTitle = $count
                ? "exception $count"
                : "exception";
            if ($exceptionCount >= 1) {
                $exTitle = $exceptionCount == 1
                    ? 'prev-ex.'
                    : "prev-ex. $exceptionCount";
            }

            $exceptionHelper = new ExceptionHelper($e, $baseDir);

            $table->row($exTitle, $exceptionHelper->renderTypeAndMessage());
            $appLocation = $exceptionHelper->renderAppLocation();

            if ($exceptionHelper->hasAppLocation()) {
                $table->row(Settings::INDENT1 . 'location', $appLocation);
            }

            $vendorLocation = $exceptionHelper->renderRealLocation();
            if ($vendorLocation != $appLocation) {
                $table->row(Settings::INDENT1 . 'vendor', $vendorLocation);
            }

            $exceptionCount++;

        } while ($e = $e->getPrevious());
    }
}
