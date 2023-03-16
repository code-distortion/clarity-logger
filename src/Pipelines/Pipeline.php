<?php

namespace CodeDistortion\ClarityLogger\Pipelines;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerPipelineException;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\PipeInterface;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use Throwable;

/**
 * A simple pipeline implementation of sorts.
 */
class Pipeline
{
    /** @var array<string, mixed> The payload to send through the pipes. */
    private array $payload = [];

    /** @var PipeInterface[] The instantiated pipe objects. */
    private array $pipeInstances = [];

    /** @var Throwable[] The exceptions that occurred when running through each pipeline step. */
    private array $internalExceptions = [];



    /**
     * Specify the payload to send through the pipes.
     *
     * @param array<string, mixed> $payload The payload to send through the pipes.
     * @return $this
     */
    public function send(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }



    /**
     * Instantiate the pipes.
     *
     * @param class-string[] $pipeClasses The "pipes" to send the payload through.
     * @return $this
     */
    public function through(array $pipeClasses): self
    {
        $this->instantiatePipes($pipeClasses);

        return $this;
    }



    /**
     * Instantiate the pipeline objects.
     *
     * @param class-string[] $pipeClasses The "pipes" to send the payload through.
     * @return void
     */
    private function instantiatePipes(array $pipeClasses): void
    {
        foreach ($pipeClasses as $pipeClass) {
            $pipe = $this->instantiatePipe($pipeClass);
            if ($pipe) {
                $this->pipeInstances[] = $pipe;
            }
        }
    }

    /**
     * Instantiate a pipeline object.
     *
     * @param class-string $pipeClass The pipe to instantiate.
     * @return PipeInterface|null
     * @throws ClarityLoggerPipelineException When an invalid Pipe class is used.
     */
    private function instantiatePipe(string $pipeClass): ?PipeInterface
    {
        try {

            $pipe = Framework::depInj()->make($pipeClass, $this->payload);

            return $pipe instanceof PipeInterface
                ? $pipe
                : throw ClarityLoggerPipelineException::invalidPipeClass($pipeClass);

        } catch (Throwable $e) {
            $this->internalExceptions[] = $e;
        }

        return null;
    }



    /**
     * Define the pipes to send the payload through, and trigger the pipeline.
     *
     * @param string  $method     The pipeline method to call.
     * @param mixed[] $parameters The parameters to pass to the method being called.
     * @return $this
     * @throws ClarityLoggerPipelineException When a pipe class doesn't implement PipeInterface.
     */
    public function go(
        string $method,
        array $parameters = [],
    ): self {

        foreach ($this->pipeInstances as $pipe) {
            $this->runStep($pipe, $method, $parameters);
        }

        return $this;
    }

    /**
     * Run a step of the pipeline.
     *
     * @param PipeInterface $pipe       The pipe to send the payload through.
     * @param string        $method     The pipeline method to call.
     * @param mixed[]       $parameters The parameters to pass to the method being called.
     * @return void
     * @throws ClarityLoggerPipelineException Doesn't throw this, but phpcs expects this to be here.
     */
    private function runStep(
        PipeInterface $pipe,
        string $method = 'run',
        array $parameters = [],
    ): void {

        try {
            $toCall = [$pipe, $method];
            if (!is_callable($toCall)) {
                throw ClarityLoggerPipelineException::pipeMethodNotCallable($pipe::class, $method);
            }

            Framework::depInj()->call($toCall, $parameters);

        } catch (Throwable $e) {
            $this->internalExceptions[] = $e;
        }
    }

    /**
     * Retrieve the exceptions that were generated when running the pipeline steps.
     *
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->internalExceptions;
    }
}
