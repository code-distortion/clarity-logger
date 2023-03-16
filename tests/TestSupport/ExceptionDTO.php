<?php

namespace CodeDistortion\ClarityLogger\Tests\TestSupport;

/**
 * A DTO for exceptions.
 */
class ExceptionDTO
{
    /**
     * Constructor.
     *
     * @param string  $message The exception message.
     * @param integer $code    The exception code.
     */
    public function __construct(
        public string $message,
        public int $code = 0,
    ) {
    }
}
