<?php

namespace CodeDistortion\ClarityLogger\Renderers\Laravel;

use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\ClarityContextPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\ClarityKnownIssuesPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\CommandPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\ContextArrayPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\ExceptionPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\InternalExceptionsPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\MessagePipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\OccurredAtPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\RequestPipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\TitlePipe;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\Text\UserPipe;
use CodeDistortion\ClarityLogger\Renderers\AbstractRenderer;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;

/**
 * Render debugging information about an exception or message - in a readable text format.
 */
class TextRenderer extends AbstractRenderer implements RendererInterface
{
//    /**
//     * Constructor.
//     *
//     * Properties are resolved using Laravel's dependency injection.
//     */
//    public function __construct() {
//    }



    /**
     * Get the list of pipes to use.
     *
     * @return class-string[]
     */
    protected function getPipes(): array
    {
        return [
            TitlePipe::class,
            MessagePipe::class,
            ExceptionPipe::class,
            CommandPipe::class,
//            ScheduledTaskPipe::class,
//            QueuePipe::class,
            RequestPipe::class,
            UserPipe::class,
            OccurredAtPipe::class,
            ClarityKnownIssuesPipe::class,
            ContextArrayPipe::class,
            InternalExceptionsPipe::class,
            ClarityContextPipe::class,
        ];
    }
}
