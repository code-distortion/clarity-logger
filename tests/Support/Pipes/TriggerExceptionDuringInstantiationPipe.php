<?php

namespace CodeDistortion\ClarityLogger\Tests\Support\Pipes;

/**
 * A pipe class that can't be instantiated (because it doesn't extend AbstractPipe).
 */
class TriggerExceptionDuringInstantiationPipe // does not extend AbstractPipe
{
}
