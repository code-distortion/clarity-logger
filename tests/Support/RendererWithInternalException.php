<?php

namespace CodeDistortion\ClarityLogger\Tests\Support;

use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\InternalExceptionsPipe;
use CodeDistortion\ClarityLogger\Renderers\AbstractRenderer;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;
use CodeDistortion\ClarityLogger\Tests\Support\Pipes\JustALinePipe;
use CodeDistortion\ClarityLogger\Tests\Support\Pipes\TriggerExceptionDuringInstantiationPipe;
use CodeDistortion\ClarityLogger\Tests\Support\Pipes\TriggerExceptionDuringInternalExceptionOccurredMethodPipe;
use CodeDistortion\ClarityLogger\Tests\Support\Pipes\TriggerExceptionDuringNoInternalExceptionOccurredMethodPipe;
use CodeDistortion\ClarityLogger\Tests\Support\Pipes\TriggerExceptionDuringRunPipe;

/**
 * A Renderer with a pipeline that generates an internal exception.
 */
class RendererWithInternalException extends AbstractRenderer implements RendererInterface
{
    /**
     * Get the list of pipes to use.
     *
     * @return class-string[]
     */
    protected function getPipes(): array
    {
        return [
            JustALinePipe::class,

            // this one won't be instantiated
            TriggerExceptionDuringInstantiationPipe::class,
            // exception thrown in run() method
            TriggerExceptionDuringRunPipe::class,
            // exception thrown in noInternalExceptionOccurred() method
            TriggerExceptionDuringNoInternalExceptionOccurredMethodPipe::class,
            // exception thrown in internalExceptionOccurred() method
            TriggerExceptionDuringInternalExceptionOccurredMethodPipe::class,

            JustALinePipe::class,
            InternalExceptionsPipe::class,
            JustALinePipe::class,
        ];
    }
}
