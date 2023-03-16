<?php

namespace CodeDistortion\ClarityLogger\Renderers;

use CodeDistortion\ClarityLogger\Helpers\PrefixHelper;
use CodeDistortion\ClarityLogger\Pipelines\Pipeline;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use Throwable;

/**
 * Render debugging information about an exception or message.
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     * Get the list of pipes to use.
     *
     * @return class-string[]
     */
    abstract protected function getPipes(): array;



    /**
     * Render the information for logging.
     *
     * @param PipelineInput $input The inputs to use.
     * @return string
     */
    public function render(PipelineInput $input): string
    {
        $output = new PipelineOutput();
        $pipeline = new Pipeline();
        $exceptions = [];

        // initial pipeline run
        try {
            $pipeline->send(['input' => $input, 'output' => $output])
                ->through($this->getPipes())
                ->go('run');
        } catch (Throwable $e) {
            $exceptions[] = $e;
        } finally {
            $exceptions = [...$pipeline->getExceptions(), ...$exceptions];
        }

        // follow-up pipeline run (now that we know whether internal exceptions occurred or not)
        try {
            count($exceptions)
                ? $pipeline->go('internalExceptionOccurred', ['exceptions' => $exceptions])
                : $pipeline->go('noInternalExceptionOccurred');
        } catch (Throwable $e) {
//            dump("Exception: \"{$e->getMessage()}\" in {$e->getFile()}:{$e->getLine()}");
        }

        // convert rendered output to string (or array?)
        try {
            $rendered = $output->getCombinedOutput();
        } catch (Throwable) {
            $rendered = '';
        }

        return $this->processOutput($input, $rendered);
    }

    /**
     * Take the pipeline output and process it ready for returning.
     *
     * @param PipelineInput $input  The input that was passed through to the pipeline.
     * @param string        $output The output that collected output from the pipeline.
     * @return string
     */
    private function processOutput(PipelineInput $input, string $output): string
    {
        return $this->postProcessString($input, $output);
    }



    /**
     * Process the output string before returning.
     *
     * @param PipelineInput $input  The input that was passed through to the pipeline.
     * @param string        $string The string to process.
     * @return string
     */
    private function postProcessString(PipelineInput $input, string $string): string
    {
        $replacements = [
            '%level%' => $input->getLevel(),
            '%LEVEL%' => mb_strtoupper($input->getLevel()),
        ];

        $prefix = str_replace(array_keys($replacements), array_values($replacements), $input->getPrefix());

        return $this->addPrefix($string, $prefix);
    }

    /**
     * Process the output string before returning.
     *
     * @param string $string The string to process.
     * @param string $prefix The prefix to add to every line.
     * @return string
     */
    private function addPrefix(string $string, string $prefix): string
    {
        if (!mb_strlen($prefix)) {
            return $string;
        }

        return PHP_EOL
            . PHP_EOL
            . PrefixHelper::add($prefix, PHP_EOL . $string . PHP_EOL)
            . PHP_EOL;
    }
}
