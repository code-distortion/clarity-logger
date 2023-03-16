<?php

namespace CodeDistortion\ClarityLogger\Renderers;

use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;

/**
 * Render debugging information about an exception or custom message.
 */
interface RendererInterface
{
    /**
     * Render the information for logging.
     *
     * @param PipelineInput $input The inputs to use.
     * @return string
     */
    public function render(PipelineInput $input): string;
}
