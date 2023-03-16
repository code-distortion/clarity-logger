<?php

namespace CodeDistortion\ClarityLogger\Output;

/**
 * An interface for output classes.
 */
interface OutputInterface
{
    /**
     * Render the content held within this object.
     *
     * @return mixed
     */
    public function render(): mixed;
}
