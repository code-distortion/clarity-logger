<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityContext\Context;
use CodeDistortion\ClarityContext\Support\CallStack\MetaData\ContextMeta;
use CodeDistortion\ClarityContext\Support\CallStack\MetaData\ExceptionCaughtMeta;
use CodeDistortion\ClarityContext\Support\CallStack\MetaData\ExceptionThrownMeta;
use CodeDistortion\ClarityContext\Support\CallStack\MetaData\Meta;
use CodeDistortion\ClarityContext\Support\CallStack\MetaGroup;
use CodeDistortion\ClarityLogger\Helpers\FileHelper;
use CodeDistortion\ClarityLogger\Helpers\MethodHelper;
use CodeDistortion\ClarityLogger\Helpers\VarExportHelper;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use CodeDistortion\ClarityLogger\Settings;

/**
 * Render the context details reported by Clarity Context.
 */
class ClarityContextPipe extends AbstractPipe
{
    /** @var boolean|null Allow tests to override the detailsAreWorthListing() check. */
    private ?bool $overrideDetailsAreWorthListing = null;

    /** @var boolean|null Allow tests to override the $showAsLastAppFrame check. */
    private ?bool $overrideShowAsLastAppFrame = null;



    /**
     * Constructor.
     *
     * Properties are resolved using Laravel's dependency injection.
     *
     * @param PipelineInput  $input  The input being reported.
     * @param PipelineOutput $output The object managing the output.
     */
    public function __construct(
        private PipelineInput $input,
        private PipelineOutput $output,
    ) {
    }



    /**
     * Determine if this pipe step should be run.
     *
     * @return boolean
     */
    private function shouldRun(): bool
    {
        if (is_null($this->input->getClarityContext())) {
            return false;
        }

        if (!is_null($this->overrideDetailsAreWorthListing)) {
            return $this->overrideDetailsAreWorthListing;
        }

        return $this->input->getClarityContext()->detailsAreWorthListing();
    }



    /**
     * Run the pipe step.
     *
     * @return void
     */
    public function run(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $this->output->reuseTextOrNew()->line('CONTEXT DETAILS:')
            ->blankLine()
            ->lines(implode(PHP_EOL . PHP_EOL, $this->generateMetaGroupDescriptions()));
    }



    /**
     * Generate descriptions of each MetaGroup
     *
     * @return string[]
     */
    private function generateMetaGroupDescriptions(): array
    {
        /** @var Context $context It is a Context object by this point. */
        $context = $this->input->getClarityContext();

        $descriptions = [];
        foreach ($this->pickMetaGroups($context) as $metaGroup) {

            $showAsLastAppFrame = $metaGroup->isInLastApplicationFrame() && !$metaGroup->isInLastFrame();

            $lines = $this->describeMetaObjects($metaGroup->getMeta());

            if ((count($lines)) || ($showAsLastAppFrame)) {

                if (!is_null($this->overrideShowAsLastAppFrame)) {
                    $showAsLastAppFrame = $this->overrideShowAsLastAppFrame;
                }

                $message = FileHelper::renderLocation(
                    $metaGroup->getProjectFile(),
                    $metaGroup->getLine(),
                    MethodHelper::resolveCurrentMethod($metaGroup->getClass(), $metaGroup->getFunction()),
                    $showAsLastAppFrame
                );
                array_unshift($lines, $message);
            }

            $descriptions[] = implode(PHP_EOL, array_filter($lines));
        }

        return array_filter($descriptions);
    }

    /**
     * Generate the MetaGroup to use.
     *
     * @param Context $context The Clarity Context object to use.
     * @return MetaGroup[]
     */
    private function pickMetaGroups(Context $context): array
    {
        return $this->input->getUseCallStackOrder()
            ? $context->getCallStack()->getMetaGroups()
            : $context->getStackTrace()->getMetaGroups();
    }

    /**
     * Generate descriptions of Meta objects.
     *
     * @param Meta[] $metaObjects The Meta objects to describe.
     * @return string[]
     */
    private function describeMetaObjects(array $metaObjects): array
    {
        $lines = [];
        foreach ($metaObjects as $meta) {
            $lines[] = $this->metaDesc($meta);
        }
        return array_filter($lines);
    }

    /**
     * Generate a description for a given Meta object.
     *
     * @param Meta $meta The Meta object to generate a description for.
     * @return string|null
     */
    private function metaDesc(Meta $meta): ?string
    {
        return match (true) {
            $meta instanceof ExceptionThrownMeta => Settings::INDENT1 . 'The exception was thrown',
            $meta instanceof ExceptionCaughtMeta => Settings::INDENT1 . 'The exception was caught (by Clarity)',
            $meta instanceof ContextMeta => $this->renderContext($meta->getContext()),
            default => null
        };
    }

    /**
     * Render the context nicely.
     *
     * @param string|mixed[] $context The context to render.
     * @return string
     */
    private function renderContext(string|array $context): string
    {
        if (is_string($context)) {
            return Settings::INDENT1 . "\"$context\"";
        }

        return VarExportHelper::niceExport(
            $context,
            Settings::INDENT1,
            Settings::INDENT2
        );
    }
}
