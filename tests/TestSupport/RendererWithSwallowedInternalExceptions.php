<?php

namespace CodeDistortion\ClarityLogger\Tests\TestSupport;

use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\InternalExceptionsPipe;
use CodeDistortion\ClarityLogger\Renderers\AbstractRenderer;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;
use CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes\JustALinePipe;
use CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes\TriggerExceptionDuringInternalExceptionOccurredMethodPipe;
use CodeDistortion\ClarityLogger\Tests\TestSupport\Pipes\TriggerExceptionDuringNoInternalExceptionOccurredMethodPipe;

/**
 * A Renderer with a pipeline that triggers internal exceptions that get swallowed.
 */
class RendererWithSwallowedInternalExceptions extends AbstractRenderer implements RendererInterface
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
