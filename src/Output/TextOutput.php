<?php

namespace CodeDistortion\ClarityLogger\Output;

/**
 * Render lines of text.
 */
class TextOutput implements OutputInterface
{
    /** @var string[] The lines of text, ready for outputting. */
    private array $lines = [];



    /**
     * Constructor.
     *
     * @param string[] $lines Lines to add straight away.
     */
    public function __construct(array $lines = [])
    {
        foreach ($lines as $line) {
            $this->line($line);
        }
    }

//    /**
//     * Alternative constructor.
//     *
//     * @param string[] $lines Lines to add straight away.
//     * @return self
//     */
//    public static function new(array $lines = []): self
//    {
//        return new self($lines);
//    }



    /**
     * Add a line of text, ready to output.
     *
     * @param string $line The line to add.
     * @return $this
     */
    public function line(string $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * Add lines of content, ready to output.
     *
     * @param string|string[] $lines The lines to add.
     * @return $this
     */
    public function lines(string|array $lines): self
    {
        $lines = is_string($lines) ? explode(PHP_EOL, $lines) : $lines;

        foreach ($lines as $line) {
            $this->line($line);
        }

        return $this;
    }

    /**
     * Add a blank line, ready to output.
     *
     * @return $this
     */
    public function blankLine(): self
    {
        $this->lines[] = '';

        return $this;
    }



    /**
     * Render the content held within this object.
     *
     * @return string
     */
    public function render(): string
    {
        return implode(PHP_EOL, $this->lines);
    }
}
