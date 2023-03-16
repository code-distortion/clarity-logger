<?php

/**
 * Build a new exception, triggered in a function.
 *
 * @return Exception
 */
function newExceptionFromFunction(): Exception
{
    return new Exception('something happened');
}
