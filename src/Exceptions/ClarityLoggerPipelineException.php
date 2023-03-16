<?php

namespace CodeDistortion\ClarityLogger\Exceptions;

use CodeDistortion\ClarityLogger\Output\OutputInterface;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\PipeInterface;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;

/**
 * Exception generated when running the pipeline that generates the information to log.
 */
class ClarityLoggerPipelineException extends ClarityLoggerException
{
    /**
     * A renderer class does not implement RendererInterface.
     *
     * @param string $class The invalid class.
     * @return self
     */
    public static function invalidRendererClass(string $class): self
    {
        return new self("\"$class\" does not implement " . RendererInterface::class);
    }

    /**
     * A pipe class does not implement PipeInterface.
     *
     * @param string $class The invalid class.
     * @return self
     */
    public static function invalidPipeClass(string $class): self
    {
        return new self("\"$class\" does not implement " . PipeInterface::class);
    }

    /**
     * A Pipe class method cannot be called.
     *
     * @param string $pipeClass The pipe class used.
     * @param string $method    The method tried.
     * @return self
     */
    public static function pipeMethodNotCallable(string $pipeClass, string $method): self
    {
        return new self("The method \"$method\" on pipe \"$pipeClass\" does not exist or is not callable");
    }

    /**
     * A pipe-output class does not implement OutputInterface.
     *
     * @param string $class The invalid class.
     * @return self
     */
    public static function invalidPipeOutputClass(string $class): self
    {
        return new self("\"$class\" does not implement " . OutputInterface::class);
    }

    /**
     * Multiple response types were given.
     *
     * @param string[] $types The response types given.
     * @return self
     */
    public static function multipleResponseTypesGiven(array $types): self
    {
        sort($types);
        $types = '"' . implode(', ', $types) . '"';
        return new self("Multiple response types were given by the pipeline: $types");
    }

    /**
     * An invalid response type was given.
     *
     * @param string $type The response type given.
     * @return self
     */
    public static function invalidResponseTypeGiven(string $type): self
    {
        return new self("An invalid response type was given by the pipeline: $type");
    }
}
