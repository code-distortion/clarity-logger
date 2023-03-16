<?php

namespace CodeDistortion\ClarityLogger\Exceptions;

use CodeDistortion\ClarityLogger\Settings;

/**
 * Exception generated when initialising Clarity Logger.
 */
class ClarityLoggerInitialisationException extends ClarityLoggerException
{
    /**
     * The current framework type cannot be resolved.
     *
     * @return self
     */
    public static function unknownFramework(): self
    {
        return new self("The current framework type could not be resolved");
    }

    /**
     * An invalid level was specified.
     *
     * @param string|null $level The invalid level.
     * @return self
     */
    public static function levelNotAllowed(?string $level): self
    {
        return new self("Level \"$level\" is not allowed. Please choose from: " . implode(', ', Settings::LOG_LEVELS));
    }
}
